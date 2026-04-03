<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Overlay;

use App\Extensions\Chatbot\System\Http\Requests\Overlay\ConversationFeedbackRequest;
use App\Extensions\Chatbot\System\Http\Requests\Overlay\CustomerMessageRequest;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use App\Extensions\Chatbot\System\Services\ConversationExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class ChatbotCustomerPortalController extends Controller
{
    public function index(): View
    {
        $workspaceId = auth()->user()?->company_id ?? auth()->user()?->team_id;

        $conversations = ChatbotConversation::query()->forWorkspace($workspaceId)
            ->whereHas('customer', fn ($q) => $q->where('user_id', auth()->id()))
            ->with(['chatbot', 'assignedAgent'])
            ->latest('last_activity_at')
            ->paginate(20);

        return view('chatbot::overlay.customer.index', compact('conversations'));
    }

    public function show(ChatbotConversation $conversation): View
    {
        $conversation->load(['chatbot', 'customer', 'assignedAgent']);
        $messages = $conversation->widgetHistories()->orderBy('created_at')->paginate(100);
        return view('chatbot::overlay.customer.show', compact('conversation', 'messages'));
    }

    public function sendMessage(CustomerMessageRequest $request, ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $attachment = $request->hasFile('attachment')
            ? $request->file('attachment')->store('chatbot-attachments', 'public')
            : null;

        $history = ChatbotHistory::create([
            'user_id' => auth()->id(),
            'chatbot_id' => $conversation->chatbot_id,
            'conversation_id' => $conversation->id,
            'message' => $request->string('message')->toString(),
            'media_name' => $attachment,
            'role' => 'user',
            'model' => 'customer-portal',
            'message_type' => $attachment ? 'file' : 'text',
            'created_at' => now(),
        ]);

        $conversation->update([
            'last_activity_at' => now(),
            'customer_read_at' => now(),
            'closed' => false,
            'closed_at' => null,
        ]);

        return $request->wantsJson()
            ? response()->json(['success' => true, 'history' => $history])
            : back()->with('success', __('Message sent.'));
    }

    public function reopen(ChatbotConversation $conversation): RedirectResponse
    {
        $conversation->update(['closed' => false, 'closed_at' => null, 'last_activity_at' => now()]);
        return back()->with('success', __('Conversation reopened.'));
    }

    public function feedback(ConversationFeedbackRequest $request, ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $conversation->update([
            'review_submitted_at' => now(),
            'review_selected_response' => $request->input('rating'),
            'review_message' => $request->input('feedback'),
        ]);

        return $request->wantsJson()
            ? response()->json(['success' => true])
            : back()->with('success', __('Feedback saved.'));
    }

    public function export(ChatbotConversation $conversation, ConversationExportService $service)
    {
        if (method_exists($service, 'download')) {
            return $service->download($conversation);
        }

        return response()->json(['conversation_id' => $conversation->id]);
    }
}
