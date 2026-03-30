<?php

namespace App\Services\Support;

use App\Models\UserSupport;
use Carbon\Carbon;

class SupportLifecycleService
{
    public function processReplies(UserSupport $ticket, string $direction): void
    {
        if ($direction === 'agent') {
            $ticket->status = 'waiting_on_user';
        } elseif ($direction === 'user') {
            $ticket->status = 'waiting_on_team';
        }

        $ticket->resolved_at = null;

        $ticket->save();
    }

    public function markStale(int $companyId, Carbon $before): int
    {
        return UserSupport::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['waiting_on_user', 'waiting_on_team'])
            ->where('updated_at', '<', $before)
            ->update(['status' => 'stale']);
    }

    public function autoResolveInactive(int $companyId, Carbon $before): int
    {
        return UserSupport::query()
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where('updated_at', '<', $before)
            ->update([
                'status'      => 'resolved',
                'resolved_at' => now(),
            ]);
    }
}
