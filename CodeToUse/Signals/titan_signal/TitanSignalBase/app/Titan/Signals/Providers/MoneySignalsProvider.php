<?php

namespace App\Titan\Signals\Providers;

use App\Titan\Signals\Signal;
use App\Titan\Signals\SignalKind;
use App\Titan\Signals\SignalProviderInterface;
use App\Titan\Signals\SignalSeverity;
use Illuminate\Support\Facades\DB;

final class MoneySignalsProvider implements SignalProviderInterface
{
    public function sourceEngine(): string
    {
        return 'money';
    }

    public function getSignals(int $companyId, ?int $teamId = null, ?int $userId = null): array
    {
        $query = DB::table('invoices')
            ->where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString());

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $overdueCount = (int) (clone $query)->count();
        $overdueCents = (int) (clone $query)->sum('amount_cents');

        if ($overdueCount > 0) {
            return [Signal::make([
                'type' => 'invoice.overdue',
                'kind' => SignalKind::OVERDUE_INVOICES,
                'severity' => SignalSeverity::RED,
                'title' => 'Overdue invoices',
                'body' => "{$overdueCount} invoices are overdue.",
                'company_id' => $companyId,
                'team_id' => $teamId,
                'user_id' => $userId,
                'payload' => ['count' => $overdueCount, 'amount_cents' => $overdueCents, 'currency' => 'AUD'],
                'meta' => ['count' => $overdueCount, 'amount_cents' => $overdueCents, 'currency' => 'AUD'],
                'source' => 'invoices',
                'origin' => 'database',
                'source_engine' => $this->sourceEngine(),
            ])];
        }

        return [Signal::make([
            'type' => 'money.ok',
            'kind' => 'money_ok',
            'severity' => SignalSeverity::GREEN,
            'title' => 'Money looks stable',
            'body' => 'No overdue invoices detected.',
            'company_id' => $companyId,
            'team_id' => $teamId,
            'user_id' => $userId,
            'payload' => [],
            'source' => 'invoices',
            'origin' => 'database',
            'source_engine' => $this->sourceEngine(),
        ])];
    }
}
