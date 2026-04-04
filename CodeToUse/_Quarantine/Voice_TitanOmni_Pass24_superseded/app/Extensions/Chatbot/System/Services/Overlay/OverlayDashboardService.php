<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Services\Overlay;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OverlayDashboardService
{
    public function paginatedChatbotsForWorkspace(?int $userId, int|string|null $workspaceId, int $perPage = 15): LengthAwarePaginator
    {
        return Chatbot::query()
            ->ownedByUser($userId, $workspaceId)
            ->with(['channels'])
            ->withCount([
                'conversations',
                'channels',
                'pageVisits',
                'conversations as open_conversations_count' => fn (Builder $q) => $q->where('closed', false),
                'conversations as assigned_conversations_count' => fn (Builder $q) => $q->whereNotNull('assigned_agent_id')->where('closed', false),
                'conversations as unassigned_conversations_count' => fn (Builder $q) => $q->whereNull('assigned_agent_id')->where('closed', false),
            ])
            ->latest('id')
            ->paginate($perPage);
    }

    public function commandSnapshot(Chatbot $chatbot): array
    {
        $conversations = $chatbot->conversations();
        $open = (clone $conversations)->where('closed', false);

        return [
            'total_conversations' => (clone $conversations)->count(),
            'open_conversations' => (clone $open)->count(),
            'closed_conversations' => (clone $conversations)->where('closed', true)->count(),
            'assigned_conversations' => (clone $open)->whereNotNull('assigned_agent_id')->count(),
            'unassigned_conversations' => (clone $open)->whereNull('assigned_agent_id')->count(),
            'voice_channels' => $chatbot->channels()->where('channel', 'voice')->count(),
            'channel_count' => $chatbot->channels()->count(),
            'page_visits' => $chatbot->pageVisits()->count(),
            'avg_first_response_seconds' => $this->averageFirstResponseSeconds($chatbot),
            'recent_channels' => $chatbot->channels()->latest('id')->take(6)->get(),
            'recent_conversations' => $chatbot->conversations()
                ->with(['customer', 'assignedAgent', 'chatbotChannel'])
                ->latest('last_activity_at')
                ->take(8)
                ->get(),
        ];
    }

    public function agentQueue(int|string|null $workspaceId, int $agentId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return ChatbotConversation::query()
            ->forWorkspace($workspaceId)
            ->with(['chatbot', 'customer', 'assignedAgent', 'lastMessage'])
            ->when(($filters['mine'] ?? false) === true, fn (Builder $q) => $q->where('assigned_agent_id', $agentId))
            ->when(($filters['unassigned'] ?? false) === true, fn (Builder $q) => $q->whereNull('assigned_agent_id'))
            ->when(($filters['closed'] ?? false) !== true, fn (Builder $q) => $q->where('closed', false))
            ->when(($filters['channel'] ?? null), fn (Builder $q, $channel) => $q->where('chatbot_channel', $channel))
            ->when(($filters['chatbot_id'] ?? null), fn (Builder $q, $chatbotId) => $q->where('chatbot_id', $chatbotId))
            ->when(($filters['search'] ?? null), function (Builder $q, string $search) {
                $q->where(function (Builder $nested) use ($search) {
                    $nested->where('conversation_name', 'like', "%{$search}%")
                        ->orWhere('customer_channel_id', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn (Builder $customer) => $customer
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw('case when assigned_agent_id = ? then 0 when assigned_agent_id is null then 1 else 2 end', [$agentId])
            ->latest('last_activity_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function customerQueue(int|string|null $workspaceId, int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return ChatbotConversation::query()
            ->forWorkspace($workspaceId)
            ->whereHas('customer', fn (Builder $q) => $q->where('user_id', $userId))
            ->with(['chatbot', 'assignedAgent', 'lastMessage'])
            ->latest('last_activity_at')
            ->paginate($perPage);
    }

    public function availableChannels(): Collection
    {
        return collect(['telegram', 'whatsapp', 'messenger', 'voice', 'generic']);
    }

    protected function averageFirstResponseSeconds(Chatbot $chatbot): ?int
    {
        $samples = [];
        $chatbot->conversations()->with(['histories' => fn ($q) => $q->orderBy('created_at')])->take(50)->get()->each(function ($conversation) use (&$samples) {
            $user = $conversation->histories->first(fn ($item) => $item->role === 'user');
            $agent = $conversation->histories->first(fn ($item) => in_array($item->role, ['assistant', 'system'], true));
            if ($user && $agent && $agent->created_at && $user->created_at && $agent->created_at->greaterThan($user->created_at)) {
                $samples[] = $user->created_at->diffInSeconds($agent->created_at);
            }
        });

        if ($samples === []) {
            return null;
        }

        return (int) round(array_sum($samples) / count($samples));
    }
}
