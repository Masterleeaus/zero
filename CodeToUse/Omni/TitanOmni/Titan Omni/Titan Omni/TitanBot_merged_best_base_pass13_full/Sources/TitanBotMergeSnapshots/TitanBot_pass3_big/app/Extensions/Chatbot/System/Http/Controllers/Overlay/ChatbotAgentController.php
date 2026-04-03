<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Overlay;

use App\Extensions\Chatbot\System\Http\Requests\Overlay\AgentReplyRequest;
use App\Extensions\Chatbot\System\Http\Requests\Overlay\TransferConversationRequest;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ChatbotAgentController extends Controller
{
    public function index(): View
    {
        $agent = auth()->user();
        $workspaceId = data_get($agent, 'workspace.id') ?? data_get($agent, 'company_id') ?? data_get($agent, 'team_id');

        $conversations = ChatbotConversation::query()->forWorkspace($workspaceId)
            ->where(function ($q) use ($agent, $workspaceId) {
                $q->where('assigned_agent_id', $agent->id)
                  ->orWhereHas('chatbot', fn ($bot) => $bot->ownedByUser($agent->id, $workspaceId));
            })
            ->with(['chatbot', 'customer', 'assignedAgent'])
            ->latest('last_activity_at')
            ->paginate(20);

        return view('chatbot::overlay.agent.index', compact('conversations'));
    }

    public function show(ChatbotConversation $conversation): View
    {
        $conversation->load(['chatbot', 'customer', 'assignedAgent']);
        $messages = $conversation->histories()->orderBy('created_at')->paginate(100);

        if (! $conversation->assigned_agent_id) {
            $conversation->update(['assigned_agent_id' => auth()->id()]);
        }

        return view('chatbot::overlay.agent.show', compact('conversation', 'messages'));
    }

    public function reply(AgentReplyRequest $request, ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $history = DB::transaction(function () use ($request, $conversation) {
            $attachment = $request->hasFile('attachment')
                ? $request->file('attachment')->store('chatbot-attachments', 'public')
                : null;

            $history = ChatbotHistory::create([
                'user_id' => auth()->id(),
                'chatbot_id' => $conversation->chatbot_id,
                'conversation_id' => $conversation->id,
                'message' => $request->string('message')->toString(),
                'media_name' => $attachment,
                'role' => 'assistant',
                'model' => 'human-agent',
                'message_type' => $attachment ? 'file' : 'text',
                'created_at' => now(),
            ]);

            $conversation->update([
                'assigned_agent_id' => auth()->id(),
                'last_activity_at' => now(),
                'connect_agent_at' => now(),
                'closed' => false,
                'closed_at' => null,
            ]);

            return $history;
        });

        return $request->wantsJson()
            ? response()->json(['success' => true, 'history' => $history])
            : back()->with('success', __('Reply sent.'));
    }

    public function transfer(TransferConversationRequest $request, ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $conversation->update([
            'assigned_agent_id' => (int) $request->integer('agent_id'),
            'last_activity_at' => now(),
        ]);

        $conversation->histories()->create([
            'user_id' => auth()->id(),
            'chatbot_id' => $conversation->chatbot_id,
            'message' => $request->input('reason', 'Conversation transferred'),
            'role' => 'assistant',
            'model' => 'human-agent',
            'message_type' => 'note',
            'is_internal_note' => true,
            'created_at' => now(),
        ]);

        return $request->wantsJson()
            ? response()->json(['success' => true])
            : back()->with('success', __('Conversation transferred.'));
    }

    public function close(ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $conversation->update(['closed' => true, 'closed_at' => now(), 'last_activity_at' => now()]);
        return request()->wantsJson()
            ? response()->json(['success' => true])
            : back()->with('success', __('Conversation closed.'));
    }

    public function unreadCount(): JsonResponse
    {
        $count = ChatbotConversation::query()->where('assigned_agent_id', auth()->id())->where('closed', false)->count();
        return response()->json(['count' => $count]);
    }
}
