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
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString());

        $overdueCount = (int) (clone $query)->count();
        $overdueAmount = (float) (clone $query)->sum('balance');
        if ($overdueAmount <= 0) {
            $overdueAmount = (float) (clone $query)->sum('total');
        }
        $overdueCents = (int) round($overdueAmount * 100);
        $overdueInvoiceIds = (array) (clone $query)->limit(10)->pluck('id')->all();
        $primaryInvoiceId = $overdueInvoiceIds[0] ?? null;

        if ($overdueCount > 0) {
            return [Signal::make([
                'type' => 'invoice.overdue',
                'kind' => SignalKind::OVERDUE_INVOICES,
                'severity' => SignalSeverity::RED,
                'title' => 'Overdue invoices',
                'body' => "{$overdueCount} invoices are overdue.",
                'company_id' => $companyId,
                'user_id' => $userId,
                'payload' => [
                    'invoice_id' => $primaryInvoiceId,
                    'count' => $overdueCount,
                    'amount_cents' => $overdueCents,
                    'currency' => 'AUD',
                    'invoice_ids' => $overdueInvoiceIds,
                ],
                'meta' => [
                    'invoice_id' => $primaryInvoiceId,
                    'count' => $overdueCount,
                    'amount_cents' => $overdueCents,
                    'currency' => 'AUD',
                    'invoice_ids' => $overdueInvoiceIds,
                ],
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
