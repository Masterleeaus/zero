<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Overlay;

use App\Extensions\Chatbot\System\Http\Requests\Overlay\AgentReplyRequest;
use App\Extensions\Chatbot\System\Http\Requests\Overlay\ClaimConversationRequest;
use App\Extensions\Chatbot\System\Http\Requests\Overlay\InternalNoteRequest;
use App\Extensions\Chatbot\System\Http\Requests\Overlay\TransferConversationRequest;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Services\Overlay\ConversationLifecycleService;
use App\Extensions\Chatbot\System\Services\Overlay\OverlayDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class ChatbotAgentController extends Controller
{
    public function __construct(
        protected OverlayDashboardService $dashboardService,
        protected ConversationLifecycleService $lifecycleService,
    ) {
    }

    public function index(): View
    {
        $agent = auth()->user();
        $workspaceId = data_get($agent, 'workspace.id') ?? data_get($agent, 'company_id') ?? data_get($agent, 'team_id');

        $filters = [
            'mine' => request()->boolean('mine'),
            'unassigned' => request()->boolean('unassigned'),
            'closed' => request()->boolean('closed'),
            'channel' => request()->string('channel')->toString() ?: null,
            'chatbot_id' => request()->integer('chatbot_id') ?: null,
            'search' => request()->string('search')->toString() ?: null,
        ];

        return view('chatbot::overlay.agent.index', [
            'conversations' => $this->dashboardService->agentQueue($workspaceId, (int) $agent->id, $filters),
            'filters' => $filters,
            'channels' => $this->dashboardService->availableChannels(),
        ]);
    }

    public function show(ChatbotConversation $conversation): View
    {
        $conversation->load(['chatbot', 'customer', 'assignedAgent']);
        $messages = $conversation->histories()->orderBy('created_at')->paginate(100);

        if (! $conversation->assigned_agent_id) {
            $conversation = $this->lifecycleService->claim($conversation, (int) auth()->id());
        }

        return view('chatbot::overlay.agent.show', compact('conversation', 'messages'));
    }

    public function claim(ClaimConversationRequest $request, ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $conversation = $this->lifecycleService->claim($conversation, (int) auth()->id());

        return $request->wantsJson()
            ? response()->json(['success' => true, 'assigned_agent_id' => $conversation->assigned_agent_id])
            : back()->with('success', __('Conversation claimed.'));
    }

    public function reply(AgentReplyRequest $request, ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $history = $this->lifecycleService->reply(
            $conversation,
            (int) auth()->id(),
            $request->string('message')->toString(),
            $request->file('attachment')
        );

        return $request->wantsJson()
            ? response()->json(['success' => true, 'history' => $history])
            : back()->with('success', __('Reply sent.'));
    }

    public function note(InternalNoteRequest $request, ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $history = $this->lifecycleService->addInternalNote(
            $conversation,
            (int) auth()->id(),
            $request->string('message')->toString()
        );

        return $request->wantsJson()
            ? response()->json(['success' => true, 'history' => $history])
            : back()->with('success', __('Internal note added.'));
    }

    public function transfer(TransferConversationRequest $request, ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $conversation = $this->lifecycleService->transfer(
            $conversation,
            (int) auth()->id(),
            (int) $request->integer('agent_id'),
            $request->input('reason')
        );

        return $request->wantsJson()
            ? response()->json(['success' => true, 'assigned_agent_id' => $conversation->assigned_agent_id])
            : back()->with('success', __('Conversation transferred.'));
    }

    public function close(ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $conversation = $this->lifecycleService->close($conversation, (int) auth()->id(), request('reason'));
        return request()->wantsJson()
            ? response()->json(['success' => true, 'closed' => $conversation->closed])
            : back()->with('success', __('Conversation closed.'));
    }

    public function unreadCount(): JsonResponse
    {
        $count = ChatbotConversation::query()
            ->where('assigned_agent_id', auth()->id())
            ->where('closed', false)
            ->where(function ($query) {
                $query->whereNull('customer_read_at')->orWhereColumn('customer_read_at', '<', 'last_activity_at');
            })
            ->count();

        return response()->json(['count' => $count]);
    }
}
