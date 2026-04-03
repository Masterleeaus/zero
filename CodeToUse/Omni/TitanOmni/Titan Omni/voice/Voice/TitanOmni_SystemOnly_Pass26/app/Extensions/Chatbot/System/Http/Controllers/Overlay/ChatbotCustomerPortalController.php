<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Overlay;

use App\Extensions\Chatbot\System\Http\Requests\Overlay\ConversationFeedbackRequest;
use App\Extensions\Chatbot\System\Http\Requests\Overlay\CreateConversationRequest;
use App\Extensions\Chatbot\System\Http\Requests\Overlay\CustomerMessageRequest;
use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotCustomer;
use App\Extensions\Chatbot\System\Services\ConversationExportService;
use App\Extensions\Chatbot\System\Services\Overlay\ConversationLifecycleService;
use App\Extensions\Chatbot\System\Services\Overlay\OverlayDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatbotCustomerPortalController extends Controller
{
    public function __construct(
        protected OverlayDashboardService $dashboardService,
        protected ConversationLifecycleService $lifecycleService,
    ) {
    }

    public function index(): View
    {
        $workspaceId = auth()->user()?->company_id ?? auth()->user()?->team_id;

        return view('chatbot::overlay.customer.index', [
            'conversations' => $this->dashboardService->customerQueue($workspaceId, (int) auth()->id()),
            'chatbots' => Chatbot::query()->ownedByUser(null, $workspaceId)->where('active', true)->get(['id', 'title']),
        ]);
    }

    public function create(CreateConversationRequest $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        $chatbot = Chatbot::query()->findOrFail($request->integer('chatbot_id'));

        $conversation = DB::transaction(function () use ($user, $chatbot, $request) {
            $customer = ChatbotCustomer::query()->firstOrCreate(
                [
                    'chatbot_id' => $chatbot->id,
                    'user_id' => $user->id,
                ],
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->mobile_number ?? $user->phone,
                    'team_id' => $chatbot->team_id,
                    'company_id' => $chatbot->company_id,
                ]
            );

            $conversation = ChatbotConversation::query()->create([
                'chatbot_customer_id' => $customer->id,
                'chatbot_channel' => 'portal',
                'customer_channel_id' => (string) $user->id,
                'conversation_name' => $request->input('subject') ?: 'Customer portal conversation',
                'chatbot_id' => $chatbot->id,
                'session_id' => (string) Str::uuid(),
                'ticket_status' => 'open',
                'last_activity_at' => now(),
                'closed' => false,
                'team_id' => $chatbot->team_id,
                'company_id' => $chatbot->company_id,
                'workspace_id' => $chatbot->workspaceKey(),
            ]);

            $this->lifecycleService->customerMessage($conversation, (int) $user->id, $request->string('message')->toString());

            return $conversation;
        });

        return $request->wantsJson()
            ? response()->json(['success' => true, 'conversation_id' => $conversation->id])
            : redirect()->route('dashboard.chatbot.overlay.customer.show', $conversation)->with('success', __('Conversation created.'));
    }

    public function show(ChatbotConversation $conversation): View
    {
        $conversation->load(['chatbot', 'customer', 'assignedAgent']);
        $messages = $conversation->widgetHistories()->orderBy('created_at')->paginate(100);
        $conversation->forceFill(['customer_read_at' => now()])->save();

        return view('chatbot::overlay.customer.show', compact('conversation', 'messages'));
    }

    public function sendMessage(CustomerMessageRequest $request, ChatbotConversation $conversation): RedirectResponse|JsonResponse
    {
        $history = $this->lifecycleService->customerMessage(
            $conversation,
            (int) auth()->id(),
            $request->string('message')->toString(),
            $request->file('attachment')
        );

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
