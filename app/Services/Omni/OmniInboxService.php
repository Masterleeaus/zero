<?php

declare(strict_types=1);

namespace App\Services\Omni;

use App\Models\Omni\OmniConversation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * OmniInboxService — agent-facing inbox management.
 *
 * Provides conversation list, claim/assign, and resolution helpers
 * for the Omni inbox dashboard. All queries include N+1 guards via
 * withInboxContext() scope.
 */
class OmniInboxService
{
    /**
     * Paginated open conversations for a company's inbox.
     *
     * @param  array<string, mixed>  $filters  Supported: agent_id, channel_type, assigned_to
     */
    public function paginatedInbox(int $companyId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return OmniConversation::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'open')
            ->when(! empty($filters['agent_id']), fn ($q) => $q->where('agent_id', $filters['agent_id']))
            ->when(! empty($filters['channel_type']), fn ($q) => $q->where('channel_type', $filters['channel_type']))
            ->when(! empty($filters['assigned_to']), fn ($q) => $q->where('assigned_to', $filters['assigned_to']))
            ->withInboxContext()
            ->orderByDesc('last_activity_at')
            ->paginate($perPage);
    }

    /**
     * Assign a conversation to a user.
     */
    public function assign(OmniConversation $conversation, int $userId): OmniConversation
    {
        $conversation->update(['assigned_to' => $userId]);
        return $conversation->fresh(['agent', 'omniCustomer', 'assignedUser']);
    }

    /**
     * Claim an unassigned conversation for the current user.
     */
    public function claim(OmniConversation $conversation, int $userId): OmniConversation
    {
        if ($conversation->assigned_to !== null) {
            throw new \RuntimeException('Conversation is already assigned to user ' . $conversation->assigned_to);
        }

        return $this->assign($conversation, $userId);
    }

    /**
     * Return recent message history for a conversation (N+1-safe).
     *
     * @return Collection<int, \App\Models\Omni\OmniMessage>
     */
    public function messageHistory(OmniConversation $conversation, int $limit = 50): Collection
    {
        return $conversation->messages()
            ->with('attachments')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Count open conversations per agent for dashboard stats.
     *
     * @return array<int, int>  [agent_id => count]
     */
    public function openCountsPerAgent(int $companyId): array
    {
        return OmniConversation::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'open')
            ->whereNotNull('agent_id')
            ->selectRaw('agent_id, COUNT(*) as total')
            ->groupBy('agent_id')
            ->pluck('total', 'agent_id')
            ->toArray();
    }
}
