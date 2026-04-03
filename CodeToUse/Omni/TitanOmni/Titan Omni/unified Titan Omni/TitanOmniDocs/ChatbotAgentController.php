<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use App\Events\ChatbotConversationAssigned;
use App\Events\AgentMessageSent;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ChatbotAgentController extends Controller
{
    /**
     * Display agent's active conversations
     */
    public function index(): View
    {
        $workspace = auth()->user()->workspace;
        $agent = auth()->user();

        // Get assigned conversations
        $conversations = ChatbotConversation::whereHas('chatbot', function ($query) use ($workspace) {
            $query->where('workspace_id', $workspace->id);
        })
        ->where('assigned_agent_id', $agent->id)
        ->where('closed', false)
        ->with(['chatbot', 'customer', 'histories' => function ($query) {
            $query->latest()->limit(1);
        }])
        ->latest('last_activity_at')
        ->paginate(20);

        // Get unassigned conversations in workspace
        $unassignedCount = ChatbotConversation::whereHas('chatbot', function ($query) use ($workspace) {
            $query->where('workspace_id', $workspace->id);
        })
        ->where('assigned_agent_id', null)
        ->where('closed', false)
        ->count();

        return view('dashboard.agent.index', [
            'conversations' => $conversations,
            'unassigned_count' => $unassignedCount,
            'stats' => [
                'active' => $conversations->total(),
                'unassigned' => $unassignedCount,
            ],
        ]);
    }

    /**
     * Display a single conversation with full thread
     */
    public function show(ChatbotConversation $conversation): View
    {
        $agent = auth()->user();
        $workspace = $agent->workspace;

        // Authorization: agent must own conversation or be admin
        if (
            $conversation->assigned_agent_id !== $agent->id
            && !Gate::allows('manage-all-conversations')
            && $conversation->chatbot->workspace_id !== $workspace->id
        ) {
            abort(403, 'Unauthorized to view this conversation');
        }

        // Auto-assign if not already assigned and agent is allowed
        if (!$conversation->assigned_agent_id) {
            $conversation->update(['assigned_agent_id' => $agent->id]);
            broadcast(new ChatbotConversationAssigned($conversation, $agent));
        }

        // Get full message history
        $messages = ChatbotHistory::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->paginate(50, ['*'], 'page', request()->input('page', 1));

        // Mark as read
        ChatbotHistory::where('conversation_id', $conversation->id)
            ->where('role', 'user')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('dashboard.agent.show', [
            'conversation' => $conversation->load(['chatbot', 'customer', 'assignedAgent']),
            'messages' => $messages,
            'agent' => $agent,
        ]);
    }

    /**
     * Agent sends a reply message
     */
    public function reply(Request $request, ChatbotConversation $conversation): JsonResponse | RedirectResponse
    {
        $agent = auth()->user();
        $workspace = $agent->workspace;

        // Authorization
        if (
            $conversation->assigned_agent_id !== $agent->id
            && !Gate::allows('manage-all-conversations')
        ) {
            abort(403, 'Not assigned to this conversation');
        }

        if ($conversation->chatbot->workspace_id !== $workspace->id) {
            abort(403, 'Workspace mismatch');
        }

        if ($conversation->closed) {
            $message = 'Conversation is closed';
            if ($request->wantsJson()) {
                return response()->json(['error' => $message], 400);
            }
            return back()->withErrors($message);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:50000',
        ]);

        $attachment = null;
        if ($request->hasFile('attachment')) {
            // Store file and get path
            $attachment = $request->file('attachment')->store('chatbot-attachments', 'public');
        }

        // Log the message in a transaction
        $history = DB::transaction(function () use ($conversation, $validated, $attachment, $agent) {
            // Create history record
            $record = ChatbotHistory::create([
                'conversation_id' => $conversation->id,
                'message' => $validated['message'],
                'media_name' => $attachment,
                'role' => 'assistant',
                'model' => 'human-agent',
                'interaction_type' => 'AGENT_REPLY',
            ]);

            // Update conversation timestamp
            $conversation->update([
                'last_activity_at' => now(),
                'connect_agent_at' => now(),
            ]);

            // Log activity
            activity()
                ->causedBy($agent)
                ->performedOn($conversation)
                ->event('agent_reply')
                ->log('Agent replied to conversation');

            return $record;
        });

        // Send message via appropriate channel
        try {
            $this->sendViaChannel($conversation, $validated['message'], $attachment);
        } catch (\Exception $e) {
            activity()
                ->causedBy($agent)
                ->performedOn($conversation)
                ->event('send_failed')
                ->log("Failed to send: {$e->getMessage()}");
        }

        // Broadcast to customer (real-time notification)
        broadcast(new AgentMessageSent($conversation, $history, $agent));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $history,
            ]);
        }

        return back()->with('success', 'Message sent');
    }

    /**
     * Transfer conversation to another agent
     */
    public function transfer(Request $request, ChatbotConversation $conversation): RedirectResponse | JsonResponse
    {
        $agent = auth()->user();
        $workspace = $agent->workspace;

        // Authorization
        if (
            $conversation->assigned_agent_id !== $agent->id
            && !Gate::allows('manage-all-conversations')
        ) {
            abort(403, 'Cannot transfer conversations you don\'t own');
        }

        if ($conversation->chatbot->workspace_id !== $workspace->id) {
            abort(403, 'Workspace mismatch');
        }

        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $newAgent = $workspace->users()->findOrFail($validated['agent_id']);

        if ($newAgent->id === $agent->id) {
            $error = 'Cannot transfer to yourself';
            if ($request->wantsJson()) {
                return response()->json(['error' => $error], 422);
            }
            return back()->withErrors($error);
        }

        // Transfer
        DB::transaction(function () use ($conversation, $newAgent, $agent, $validated) {
            $conversation->update(['assigned_agent_id' => $newAgent->id]);

            // Log transfer
            ChatbotHistory::create([
                'conversation_id' => $conversation->id,
                'message' => "Conversation transferred from {$agent->name} to {$newAgent->name}. Reason: {$validated['reason']}",
                'role' => 'system',
                'model' => null,
                'interaction_type' => 'TRANSFER',
            ]);

            activity()
                ->causedBy($agent)
                ->performedOn($conversation)
                ->event('conversation_transferred')
                ->withProperties(['from_agent_id' => $agent->id, 'to_agent_id' => $newAgent->id])
                ->log("Transferred to {$newAgent->name}");
        });

        // Notify new agent
        broadcast(new ChatbotConversationAssigned($conversation, $newAgent));

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('dashboard.agent.index')
            ->with('success', "Transferred to {$newAgent->name}");
    }

    /**
     * Close/resolve a conversation
     */
    public function close(Request $request, ChatbotConversation $conversation): RedirectResponse | JsonResponse
    {
        $agent = auth()->user();

        if ($conversation->assigned_agent_id !== $agent->id && !Gate::allows('manage-all-conversations')) {
            abort(403);
        }

        $validated = $request->validate([
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($conversation, $validated, $agent) {
            $conversation->update([
                'closed' => true,
                'closed_at' => now(),
            ]);

            // Log closing message
            ChatbotHistory::create([
                'conversation_id' => $conversation->id,
                'message' => $validated['resolution_notes'] ?? 'Conversation closed by agent',
                'role' => 'system',
                'model' => null,
                'interaction_type' => 'CLOSE',
            ]);

            activity()
                ->causedBy($agent)
                ->performedOn($conversation)
                ->event('conversation_closed')
                ->log('Conversation closed');
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('dashboard.agent.index')
            ->with('success', 'Conversation closed');
    }

    /**
     * Get list of active conversations (API)
     */
    public function list(Request $request): JsonResponse
    {
        $agent = auth()->user();
        $workspace = $agent->workspace;

        $filter = $request->input('filter', 'active'); // active, closed, all
        $query = ChatbotConversation::whereHas('chatbot', function ($q) use ($workspace) {
            $q->where('workspace_id', $workspace->id);
        });

        if ($filter === 'active') {
            $query->where('closed', false);
        } elseif ($filter === 'closed') {
            $query->where('closed', true);
        }

        // Only show conversations assigned to this agent (unless admin)
        if (!Gate::allows('manage-all-conversations')) {
            $query->where('assigned_agent_id', $agent->id);
        }

        $conversations = $query
            ->with(['chatbot', 'customer', 'assignedAgent', 'histories' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->latest('last_activity_at')
            ->paginate($request->input('per_page', 50));

        return response()->json($conversations);
    }

    /**
     * Mark conversation as read
     */
    public function markRead(Request $request, ChatbotConversation $conversation): JsonResponse
    {
        $agent = auth()->user();

        if ($conversation->assigned_agent_id !== $agent->id && !Gate::allows('manage-all-conversations')) {
            abort(403);
        }

        ChatbotHistory::where('conversation_id', $conversation->id)
            ->where('role', 'user')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread count
     */
    public function unreadCount(): JsonResponse
    {
        $agent = auth()->user();
        $workspace = $agent->workspace;

        $count = ChatbotHistory::whereHas('conversation', function ($query) use ($workspace, $agent) {
            $query->whereHas('chatbot', function ($q) use ($workspace) {
                $q->where('workspace_id', $workspace->id);
            })
            ->where('closed', false)
            ->where(function ($q) use ($agent) {
                $q->where('assigned_agent_id', $agent->id)
                  ->orWhere('assigned_agent_id', null);
            });
        })
        ->where('role', 'user')
        ->whereNull('read_at')
        ->count();

        return response()->json(['unread' => $count]);
    }

    /**
     * Send message via appropriate channel service
     */
    private function sendViaChannel(ChatbotConversation $conversation, string $message, ?string $attachment = null): void
    {
        $channel = $conversation->channel_type;
        $customerId = $conversation->customer->channel_identifier ?? $conversation->customer->id;

        match($channel) {
            'telegram' => app(\App\Extensions\ChatbotTelegram\System\Services\Telegram\TelegramService::class)
                ->setChannel($conversation->chatbot->channels()->where('channel_type', 'telegram')->first())
                ->sendText($message, $customerId),
            
            'whatsapp' => app(\App\Extensions\ChatbotWhatsapp\System\Services\Twillio\TwilioWhatsappService::class)
                ->setChannel($conversation->chatbot->channels()->where('channel_type', 'whatsapp')->first())
                ->sendText($message, $customerId),
            
            'messenger' => app(\App\Extensions\ChatbotMessenger\System\Services\MessengerConversationService::class)
                ->setChannel($conversation->chatbot->channels()->where('channel_type', 'messenger')->first())
                ->sendText($message, $customerId),
            
            'voice' => app(\App\Extensions\ChatbotVoice\System\Services\VoiceService::class)
                ->setChannel($conversation->chatbot->channels()->where('channel_type', 'voice')->first())
                ->sendAudio($message, $customerId),
            
            default => null,
        };
    }
}
