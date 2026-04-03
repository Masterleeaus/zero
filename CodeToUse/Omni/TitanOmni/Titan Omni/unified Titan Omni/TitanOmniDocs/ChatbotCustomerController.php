<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use App\Extensions\Chatbot\System\Models\ChatbotCustomer;
use App\Extensions\Chatbot\System\Services\GeneratorService;
use App\Events\CustomerMessageReceived;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ChatbotCustomerController extends Controller
{
    /**
     * Display all conversations for the customer
     */
    public function index(): View
    {
        $customer = auth()->user();

        $conversations = ChatbotConversation::where('chatbot_customer_id', $customer->id)
            ->with([
                'chatbot:id,name,avatar',
                'assignedAgent:id,name,email',
                'histories' => function ($query) {
                    $query->latest()->limit(1);
                },
            ])
            ->orderBy('last_activity_at', 'desc')
            ->paginate(20);

        return view('portal.conversations.index', [
            'conversations' => $conversations,
            'stats' => [
                'total' => ChatbotConversation::where('chatbot_customer_id', $customer->id)->count(),
                'open' => ChatbotConversation::where('chatbot_customer_id', $customer->id)
                    ->where('closed', false)
                    ->count(),
                'closed' => ChatbotConversation::where('chatbot_customer_id', $customer->id)
                    ->where('closed', true)
                    ->count(),
            ],
        ]);
    }

    /**
     * Display a specific conversation with full thread
     */
    public function show(ChatbotConversation $conversation): View
    {
        $customer = auth()->user();

        // Authorization: customer can only see own conversations
        if ($conversation->chatbot_customer_id !== $customer->id) {
            abort(403, 'Unauthorized to view this conversation');
        }

        // Get messages with pagination
        $messages = ChatbotHistory::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->paginate(50, ['*'], 'page', request()->input('page', 1));

        // Mark messages as read by customer
        ChatbotHistory::where('conversation_id', $conversation->id)
            ->where('role', 'assistant')
            ->whereNull('customer_read_at')
            ->update(['customer_read_at' => now()]);

        return view('portal.conversations.show', [
            'conversation' => $conversation->load([
                'chatbot:id,name,description,avatar,logo',
                'assignedAgent:id,name,email,avatar',
            ]),
            'messages' => $messages,
            'agent' => $conversation->assignedAgent,
            'isClosed' => $conversation->closed,
        ]);
    }

    /**
     * Customer sends a new message
     */
    public function sendMessage(Request $request, ChatbotConversation $conversation): JsonResponse | RedirectResponse
    {
        $customer = auth()->user();

        // Authorization
        if ($conversation->chatbot_customer_id !== $customer->id) {
            abort(403, 'Unauthorized');
        }

        // Check if conversation is closed
        if ($conversation->closed) {
            $error = 'This conversation has been closed. ';
            if ($request->wantsJson()) {
                return response()->json(['error' => $error], 400);
            }
            return back()->withErrors($error);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:50000',
        ]);

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment')->store('chatbot-attachments', 'public');
        }

        // Process message in transaction
        $history = DB::transaction(function () use ($conversation, $validated, $attachment, $customer) {
            // Create customer message record
            $customerMessage = ChatbotHistory::create([
                'conversation_id' => $conversation->id,
                'message' => $validated['message'],
                'media_name' => $attachment,
                'role' => 'user',
                'model' => null,
                'interaction_type' => 'CUSTOMER_MESSAGE',
            ]);

            // Update conversation activity
            $conversation->update(['last_activity_at' => now()]);

            return $customerMessage;
        });

        // Route to agent or AI based on assignment
        if ($conversation->assigned_agent_id) {
            // Notify agent
            broadcast(new CustomerMessageReceived($conversation, $history));
        } else {
            // Generate AI response
            try {
                $response = app(GeneratorService::class)->generate(
                    $validated['message'],
                    $conversation->chatbot
                );

                $aiMessage = ChatbotHistory::create([
                    'conversation_id' => $conversation->id,
                    'message' => $response,
                    'role' => 'assistant',
                    'model' => $conversation->chatbot->ai_model,
                    'interaction_type' => 'AI_RESPONSE',
                ]);

                // Broadcast AI response
                broadcast(new MessageSent($conversation, $aiMessage));
            } catch (\Exception $e) {
                \Log::error('ChatBot AI Generation Error', [
                    'conversation_id' => $conversation->id,
                    'error' => $e->getMessage(),
                ]);

                // Fallback response
                ChatbotHistory::create([
                    'conversation_id' => $conversation->id,
                    'message' => 'Sorry, I couldn\'t generate a response. An agent will be with you shortly.',
                    'role' => 'assistant',
                    'model' => 'system',
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $history,
            ]);
        }

        return back();
    }

    /**
     * Create a new conversation (customer initiates from widget/portal)
     */
    public function create(Request $request): JsonResponse | RedirectResponse
    {
        $validated = $request->validate([
            'chatbot_id' => 'required|exists:ext_chatbots,id',
            'initial_message' => 'required|string|max:5000',
            'email' => 'nullable|email',
            'name' => 'nullable|string|max:255',
        ]);

        $customer = auth()->user();
        $chatbot = \App\Extensions\Chatbot\System\Models\Chatbot::findOrFail($validated['chatbot_id']);

        // Create or find customer record
        $chatbotCustomer = ChatbotCustomer::firstOrCreate([
            'chatbot_id' => $chatbot->id,
            'user_id' => $customer->id,
        ], [
            'channel_identifier' => $customer->email,
        ]);

        // Create conversation
        $conversation = DB::transaction(function () use ($chatbot, $chatbotCustomer, $validated) {
            $conv = ChatbotConversation::create([
                'chatbot_id' => $chatbot->id,
                'chatbot_customer_id' => $chatbotCustomer->id,
                'channel_type' => 'portal',
                'last_activity_at' => now(),
            ]);

            // Log initial message
            ChatbotHistory::create([
                'conversation_id' => $conv->id,
                'message' => $validated['initial_message'],
                'role' => 'user',
                'interaction_type' => 'CUSTOMER_MESSAGE',
            ]);

            return $conv;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id,
                'redirect' => route('portal.conversations.show', $conversation),
            ]);
        }

        return redirect()->route('portal.conversations.show', $conversation);
    }

    /**
     * Get conversation list (API)
     */
    public function list(Request $request): JsonResponse
    {
        $customer = auth()->user();

        $conversations = ChatbotConversation::where('chatbot_customer_id', $customer->id)
            ->with([
                'chatbot:id,name,avatar',
                'assignedAgent:id,name',
                'histories' => function ($q) {
                    $q->latest()->limit(1);
                },
            ])
            ->orderBy('last_activity_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json($conversations);
    }

    /**
     * Get a single conversation (API)
     */
    public function getConversation(ChatbotConversation $conversation): JsonResponse
    {
        $customer = auth()->user();

        if ($conversation->chatbot_customer_id !== $customer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'conversation' => $conversation,
            'messages' => ChatbotHistory::where('conversation_id', $conversation->id)
                ->orderBy('created_at', 'asc')
                ->paginate(50),
        ]);
    }

    /**
     * Reopen a closed conversation
     */
    public function reopen(Request $request, ChatbotConversation $conversation): RedirectResponse | JsonResponse
    {
        $customer = auth()->user();

        if ($conversation->chatbot_customer_id !== $customer->id) {
            abort(403);
        }

        if (!$conversation->closed) {
            $error = 'Conversation is already open';
            if ($request->wantsJson()) {
                return response()->json(['error' => $error], 422);
            }
            return back()->withErrors($error);
        }

        $conversation->update([
            'closed' => false,
            'closed_at' => null,
        ]);

        ChatbotHistory::create([
            'conversation_id' => $conversation->id,
            'message' => 'Conversation reopened by customer',
            'role' => 'system',
            'interaction_type' => 'REOPEN',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('portal.conversations.show', $conversation)
            ->with('success', 'Conversation reopened');
    }

    /**
     * Rate or provide feedback on a conversation
     */
    public function rateFeedback(Request $request, ChatbotConversation $conversation): JsonResponse
    {
        $customer = auth()->user();

        if ($conversation->chatbot_customer_id !== $customer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        // Store rating (you might want a separate table for this)
        // For now, we'll store it as a system message
        ChatbotHistory::create([
            'conversation_id' => $conversation->id,
            'message' => "Customer Rating: {$validated['rating']}/5\n\nFeedback: {$validated['feedback']}",
            'role' => 'system',
            'interaction_type' => 'RATING',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
        ]);
    }

    /**
     * Export conversation as PDF or CSV
     */
    public function export(Request $request, ChatbotConversation $conversation): \Illuminate\Http\Response
    {
        $customer = auth()->user();

        if ($conversation->chatbot_customer_id !== $customer->id) {
            abort(403);
        }

        $format = $request->input('format', 'pdf'); // pdf or csv

        $messages = ChatbotHistory::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($format === 'csv') {
            return $this->exportCsv($conversation, $messages);
        }

        return $this->exportPdf($conversation, $messages);
    }

    /**
     * Export conversation as CSV
     */
    private function exportCsv(ChatbotConversation $conversation, $messages): \Illuminate\Http\Response
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="conversation_' . $conversation->id . '.csv"',
        ];

        $callback = function() use ($messages) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Sender', 'Message']);

            foreach ($messages as $message) {
                fputcsv($file, [
                    $message->created_at->format('Y-m-d H:i:s'),
                    ucfirst($message->role),
                    $message->message,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export conversation as PDF
     */
    private function exportPdf(ChatbotConversation $conversation, $messages)
    {
        // You would use a PDF library like TCPDF, mPDF, or Dompdf here
        // Example using Dompdf:
        
        $html = view('portal.conversations.export-pdf', [
            'conversation' => $conversation,
            'messages' => $messages,
        ])->render();

        $pdf = \PDF::loadHTML($html);
        
        return $pdf->download('conversation_' . $conversation->id . '.pdf');
    }

    /**
     * Get suggested quick replies for customer
     */
    public function quickReplies(ChatbotConversation $conversation): JsonResponse
    {
        $customer = auth()->user();

        if ($conversation->chatbot_customer_id !== $customer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get last assistant message to provide context-aware replies
        $lastMessage = ChatbotHistory::where('conversation_id', $conversation->id)
            ->where('role', 'assistant')
            ->latest()
            ->first();

        $replies = [
            'Yes',
            'No',
            'Thank you',
            'Can I speak to an agent?',
            'I need more help',
        ];

        // You could also fetch chatbot-defined quick replies here

        return response()->json(['replies' => $replies]);
    }
}
