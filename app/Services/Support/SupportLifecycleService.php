<?php

namespace App\Services\Support;

use App\Models\UserSupport;
use App\Models\User;
use App\Notifications\LiveNotification;
use Carbon\Carbon;

class SupportLifecycleService
{
    public function processReplies(UserSupport $ticket, string $direction): UserSupport
    {
        if ($direction === 'agent') {
            $ticket->status = 'waiting_on_user';
        } elseif ($direction === 'user') {
            $ticket->status = 'waiting_on_team';
        }

        $ticket->resolved_at = null;

        $ticket->save();

        if (in_array($direction, ['agent', 'user'], true)) {
            $this->notifyOwner($ticket, $ticket->status);
        }

        return $ticket->fresh();
    }

    public function markStale(int $companyId, Carbon $before): int
    {
        $updated = 0;

        UserSupport::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['waiting_on_user', 'waiting_on_team'])
            ->where('updated_at', '<', $before)
            ->chunkById(100, function ($tickets) use (&$updated) {
                $ids = $tickets->pluck('id');

                if ($ids->isEmpty()) {
                    return;
                }

                UserSupport::query()->whereIn('id', $ids)->update(['status' => 'stale']);
                $updated += $ids->count();

                $tickets->each(function (UserSupport $ticket) {
                    $this->notifyOwner($ticket, 'stale');
                });
            });

        return $updated;
    }

    public function autoResolveInactive(int $companyId, Carbon $before): int
    {
        $updated = 0;
        $resolvedAt = now();

        UserSupport::query()
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where('updated_at', '<', $before)
            ->chunkById(100, function ($tickets) use (&$updated, $resolvedAt) {
                $ids = $tickets->pluck('id');

                if ($ids->isEmpty()) {
                    return;
                }

                UserSupport::query()
                    ->whereIn('id', $ids)
                    ->update([
                        'status'      => 'resolved',
                        'resolved_at' => $resolvedAt,
                    ]);

                $updated += $ids->count();

                $tickets->each(function (UserSupport $ticket) use ($resolvedAt) {
                    $ticket->resolved_at = $resolvedAt;
                    $this->notifyOwner($ticket, 'resolved');
                });
            });

        return $updated;
    }

    public function resolve(UserSupport $ticket): UserSupport
    {
        $ticket->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);

        $this->notifyOwner($ticket, 'resolved');

        return $ticket->fresh();
    }

    public function assignTo(UserSupport $ticket, int $userId): UserSupport
    {
        $ticket->update([
            'assigned_to' => $userId,
            'status'      => 'waiting_on_team',
        ]);

        $assignee = User::where('company_id', $ticket->company_id)
            ->whereKey($userId)
            ->first();
        $assignee?->notify(new LiveNotification(
            message: "Support ticket assigned to you: {$ticket->subject}",
            link: route('dashboard.support.view', $ticket),
            title: 'Ticket Assigned'
        ));

        $this->notifyOwner($ticket, 'waiting_on_team');

        return $ticket->fresh();
    }

    public function escalate(UserSupport $ticket, string $reason = ''): UserSupport
    {
        $ticket->update(['priority' => 'high', 'status' => 'escalated']);

        $this->notifyOwner($ticket, 'escalated');

        $this->notifyCompanyAdmins(
            $ticket,
            "Support ticket escalated: {$ticket->subject}" . ($reason ? " ({$reason})" : ''),
            'Support Ticket Escalated'
        );

        return $ticket->fresh();
    }

    protected function notifyOwner(UserSupport $ticket, string $newStatus): void
    {
        $ticket->loadMissing('user');

        if (! $ticket->user) {
            return;
        }

        $ticket->user->notify(new LiveNotification(
            message: "Your ticket '{$ticket->subject}' status changed to: {$newStatus}",
            link: route('dashboard.support.view', $ticket),
            title: 'Ticket Updated'
        ));
    }

    /**
     * Notify company administrators about a ticket event (creation/escalation).
     * Exposed publicly to allow controllers to reuse company-scoped notifications.
     */
    public function notifyCompanyAdmins(UserSupport $ticket, string $message, string $title): void
    {
        if (! $ticket->company_id) {
            return;
        }

        $admins = User::where('company_id', $ticket->company_id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'support']))
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new LiveNotification(
                message: $message,
                link: route('dashboard.support.view', $ticket),
                title: $title
            ));
        }
    }
}
