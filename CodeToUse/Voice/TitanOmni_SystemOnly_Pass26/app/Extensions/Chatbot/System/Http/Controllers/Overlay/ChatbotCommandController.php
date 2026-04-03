<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Overlay;

use App\Extensions\Chatbot\System\Http\Requests\Overlay\ChannelCredentialRequest;
use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Services\ChatbotAnalyticsService;
use App\Extensions\Chatbot\System\Services\Overlay\ChannelCredentialService;
use App\Extensions\Chatbot\System\Services\Overlay\OverlayDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class ChatbotCommandController extends Controller
{
    public function __construct(protected OverlayDashboardService $dashboardService)
    {
    }

    public function index(): View
    {
        $user = auth()->user();
        $workspaceId = data_get($user, 'workspace.id') ?? data_get($user, 'company_id') ?? data_get($user, 'team_id');

        return view('chatbot::overlay.command.index', [
            'chatbots' => $this->dashboardService->paginatedChatbotsForWorkspace($user?->id, $workspaceId),
            'workspaceId' => $workspaceId,
            'availableChannels' => $this->dashboardService->availableChannels(),
        ]);
    }

    public function show(Chatbot $chatbot): View
    {
        $chatbot->load(['channels.webhooks', 'conversations.customer', 'conversations.assignedAgent', 'conversations.chatbotChannel']);

        return view('chatbot::overlay.command.show', [
            'chatbot' => $chatbot,
            'snapshot' => $this->dashboardService->commandSnapshot($chatbot),
            'availableChannels' => $this->dashboardService->availableChannels(),
            'conversations' => $chatbot->conversations()->with(['customer', 'assignedAgent', 'chatbotChannel'])->latest('last_activity_at')->paginate(20),
        ]);
    }

    public function analytics(Chatbot $chatbot, ChatbotAnalyticsService $analyticsService): View
    {
        return view('chatbot::overlay.command.analytics', [
            'chatbot' => $chatbot,
            'stats' => method_exists($analyticsService, 'stats') ? $analyticsService->stats($chatbot) : [],
            'snapshot' => $this->dashboardService->commandSnapshot($chatbot),
        ]);
    }

    public function saveChannel(ChannelCredentialRequest $request, Chatbot $chatbot, ChannelCredentialService $service): RedirectResponse|JsonResponse
    {
        $channel = $service->upsert(
            $chatbot,
            (string) $request->input('channel'),
            (array) $request->input('credentials', []),
            (array) $request->input('payload', [])
        );

        return $request->wantsJson()
            ? response()->json(['success' => true, 'channel_id' => $channel->id])
            : back()->with('success', __('Channel settings saved.'));
    }

    public function webhookUrl(Chatbot $chatbot, string $channel): JsonResponse
    {
        $routes = [
            'telegram' => route('api.chatbot.webhook.telegram', $chatbot),
            'whatsapp' => route('api.chatbot.webhook.whatsapp', $chatbot),
            'messenger' => route('api.chatbot.webhook.messenger', $chatbot),
            'voice' => route('api.chatbot.webhook.voice', $chatbot),
            'generic' => route('api.chatbot.webhook', ['chatbot' => $chatbot, 'channel' => $channel]),
        ];

        return response()->json([
            'channel' => $channel,
            'url' => $routes[$channel] ?? $routes['generic'],
        ]);
    }
}
