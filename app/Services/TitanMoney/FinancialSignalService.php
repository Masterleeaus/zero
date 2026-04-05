<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\JobCostAllocation;
use App\Models\Money\Payment;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;

/**
 * FinancialSignalService
 *
 * Evaluates a company's financial position and emits alert events
 * when thresholds are breached.
 *
 * Alerts emitted:
 *   FinancialRiskDetected  – general risk condition
 *   MarginDropDetected     – margin falls below threshold
 *   CashRunwayWarning      – cash buffer drops below N days
 *   CashflowRiskDetected   – projected outflow exceeds inflow
 *
 * Default thresholds (overridable per-call):
 *   cash_buffer_days_min   = 30
 *   margin_pct_min         = 10.0
 *   supplier_spike_pct     = 50.0  (% increase period-over-period)
 *   labor_anomaly_pct      = 30.0  (% increase period-over-period)
 *   expense_surge_pct      = 40.0  (% increase period-over-period)
 */
class FinancialSignalService
{
    private const DEFAULT_THRESHOLDS = [
        'cash_buffer_days_min' => 30,
        'margin_pct_min'       => 10.0,
        'supplier_spike_pct'   => 50.0,
        'labor_anomaly_pct'    => 30.0,
        'expense_surge_pct'    => 40.0,
    ];

    /**
     * Run all signal checks and return triggered alerts.
     *
     * @param  array<string, mixed>  $thresholds  Override defaults selectively
     * @return array<int, array{signal: string, severity: string, detail: array}>
     */
    public function evaluate(int $companyId, array $thresholds = []): array
    {
        $cfg     = array_merge(self::DEFAULT_THRESHOLDS, $thresholds);
        $alerts  = [];
        $kpis    = app(FinancialKpiService::class)->compute($companyId);
        $cashflow= app(CashflowService::class)->forPeriod($companyId, now()->startOfMonth(), now()->endOfMonth());

        // ── Cash buffer check ───────────────────────────────────────────────
        if ($kpis['cash_buffer_days'] !== null && $kpis['cash_buffer_days'] < $cfg['cash_buffer_days_min']) {
            $alert = [
                'signal'   => 'low_cash_buffer',
                'severity' => $kpis['cash_buffer_days'] < 7 ? 'critical' : 'warning',
                'detail'   => [
                    'cash_buffer_days'    => $kpis['cash_buffer_days'],
                    'threshold_days'      => $cfg['cash_buffer_days_min'],
                ],
            ];
            $alerts[] = $alert;
            Event::dispatch(new \App\Events\Money\CashRunwayWarning($companyId, $alert['detail']));
        }

        // ── Margin drop check ────────────────────────────────────────────────
        if ($kpis['gross_margin_pct'] < $cfg['margin_pct_min']) {
            $alert = [
                'signal'   => 'margin_drop',
                'severity' => $kpis['gross_margin_pct'] < 0 ? 'critical' : 'warning',
                'detail'   => [
                    'gross_margin_pct'  => $kpis['gross_margin_pct'],
                    'threshold_pct'     => $cfg['margin_pct_min'],
                ],
            ];
            $alerts[] = $alert;
            Event::dispatch(new \App\Events\Money\MarginDropDetected($companyId, $alert['detail']));

            if ($kpis['gross_margin_pct'] < 0) {
                Event::dispatch(new \App\Events\Money\MarginThresholdCrossed($companyId, $alert['detail']));
            }
        }

        // ── Cashflow risk: projected outflow exceeds inflow ──────────────────
        if ($cashflow['projected_outflow'] > $cashflow['projected_inflow']) {
            $alert = [
                'signal'   => 'cashflow_risk',
                'severity' => 'warning',
                'detail'   => [
                    'projected_inflow'  => $cashflow['projected_inflow'],
                    'projected_outflow' => $cashflow['projected_outflow'],
                    'net_projected'     => $cashflow['net_projected'],
                ],
            ];
            $alerts[] = $alert;
            Event::dispatch(new \App\Events\Money\CashflowRiskDetected($companyId, $alert['detail']));
        }

        // ── Supplier liability spike check (period-over-period) ──────────────
        $this->checkSupplierSpike($companyId, $cfg, $alerts);

        // ── Labor cost anomaly ───────────────────────────────────────────────
        $this->checkLaborAnomaly($companyId, $cfg, $alerts);

        // ── Expense surge ────────────────────────────────────────────────────
        $this->checkExpenseSurge($companyId, $cfg, $alerts);

        // ── Negative job margin detection ────────────────────────────────────
        $this->checkNegativeJobMargins($companyId, $alerts);

        if (! empty($alerts)) {
            Event::dispatch(new \App\Events\Money\FinancialRiskDetected($companyId, $alerts));
        }

        return $alerts;
    }

    // ------------------------------------------------------------------
    // Spike / anomaly checkers
    // ------------------------------------------------------------------

    private function checkSupplierSpike(int $companyId, array $cfg, array &$alerts): void
    {
        $currentMonth  = $this->supplierCost($companyId, now()->startOfMonth(), now()->endOfMonth());
        $previousMonth = $this->supplierCost($companyId, now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth());

        if ($previousMonth > 0) {
            $changePct = (($currentMonth - $previousMonth) / $previousMonth) * 100;
            if ($changePct >= $cfg['supplier_spike_pct']) {
                $alerts[] = [
                    'signal'   => 'supplier_liability_spike',
                    'severity' => 'warning',
                    'detail'   => [
                        'current_month_cost'  => round($currentMonth, 2),
                        'previous_month_cost' => round($previousMonth, 2),
                        'change_pct'          => round($changePct, 2),
                    ],
                ];
            }
        }
    }

    private function checkLaborAnomaly(int $companyId, array $cfg, array &$alerts): void
    {
        $current  = $this->laborCost($companyId, now()->startOfMonth(), now()->endOfMonth());
        $previous = $this->laborCost($companyId, now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth());

        if ($previous > 0) {
            $changePct = (($current - $previous) / $previous) * 100;
            if ($changePct >= $cfg['labor_anomaly_pct']) {
                $alerts[] = [
                    'signal'   => 'labor_cost_anomaly',
                    'severity' => 'warning',
                    'detail'   => [
                        'current_labor'  => round($current, 2),
                        'previous_labor' => round($previous, 2),
                        'change_pct'     => round($changePct, 2),
                    ],
                ];
            }
        }
    }

    private function checkExpenseSurge(int $companyId, array $cfg, array &$alerts): void
    {
        $current  = $this->expenseCost($companyId, now()->startOfMonth(), now()->endOfMonth());
        $previous = $this->expenseCost($companyId, now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth());

        if ($previous > 0) {
            $changePct = (($current - $previous) / $previous) * 100;
            if ($changePct >= $cfg['expense_surge_pct']) {
                $alerts[] = [
                    'signal'   => 'expense_surge',
                    'severity' => 'warning',
                    'detail'   => [
                        'current_expenses'  => round($current, 2),
                        'previous_expenses' => round($previous, 2),
                        'change_pct'        => round($changePct, 2),
                    ],
                ];
            }
        }
    }

    private function checkNegativeJobMargins(int $companyId, array &$alerts): void
    {
        $profitability = app(ProfitabilityService::class);
        $jobs = \App\Models\Work\ServiceJob::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->pluck('id');

        foreach ($jobs as $jobId) {
            $job = \App\Models\Work\ServiceJob::withoutGlobalScope('company')->find($jobId);
            if (! $job) {
                continue;
            }
            $result = $profitability->forJob($job);
            if ($result['gross_margin'] < 0) {
                $alerts[] = [
                    'signal'   => 'negative_job_margin',
                    'severity' => 'warning',
                    'detail'   => [
                        'service_job_id' => $jobId,
                        'gross_margin'   => $result['gross_margin'],
                        'margin_pct'     => $result['margin_pct'],
                    ],
                ];
            }
        }
    }

    // ------------------------------------------------------------------
    // Query helpers
    // ------------------------------------------------------------------

    private function supplierCost(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) JobCostAllocation::where('company_id', $companyId)
            ->where('cost_type', 'subcontractor')
            ->whereBetween('allocated_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    private function laborCost(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) JobCostAllocation::where('company_id', $companyId)
            ->where('cost_type', 'labour')
            ->whereBetween('allocated_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    private function expenseCost(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) Expense::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }
}
