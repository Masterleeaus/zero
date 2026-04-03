<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Overlay;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Services\ChatbotAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class ChatbotCommandController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $workspaceId = data_get($user, 'workspace.id') ?? data_get($user, 'company_id') ?? data_get($user, 'team_id');

        $chatbots = Chatbot::query()
            ->ownedByUser($user?->id, $workspaceId)
            ->with(['channels'])
            ->withCount([
                'conversations',
                'channels',
                'conversations as open_conversations_count' => fn ($q) => $q->where('closed', false),
            ])
            ->latest('id')
            ->paginate(15);

        return view('chatbot::overlay.command.index', [
            'chatbots' => $chatbots,
            'workspaceId' => $workspaceId,
        ]);
    }

    public function show(Chatbot $chatbot): View
    {
        $chatbot->load(['channels.webhooks', 'conversations.customer', 'conversations.assignedAgent', 'conversations.chatbotChannel']);

        return view('chatbot::overlay.command.show', [
            'chatbot' => $chatbot,
            'conversations' => $chatbot->conversations()->latest('last_activity_at')->paginate(20),
        ]);
    }

    public function analytics(Chatbot $chatbot, ChatbotAnalyticsService $analyticsService): View
    {
        return view('chatbot::overlay.command.analytics', [
            'chatbot' => $chatbot,
            'stats' => method_exists($analyticsService, 'stats') ? $analyticsService->stats($chatbot) : [],
        ]);
    }

    public function webhookUrl(Chatbot $chatbot, string $channel)
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
