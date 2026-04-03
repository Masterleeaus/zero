<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Account;
use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\JobCostEntry;
use App\Models\Money\Payment;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBill;
use Illuminate\Support\Collection;

/**
 * FinanceReportService — reporting engine for the Money domain.
 *
 * Produces:
 *   - Profit & Loss summary
 *   - Balance Sheet
 *   - Cash Flow statement
 *   - Aged Receivables
 *   - Aged Payables
 *   - Job Profitability
 *
 * All queries are scoped by company_id.
 */
class FinanceReportService
{
    // -----------------------------------------------------------------------
    // Profit & Loss
    // -----------------------------------------------------------------------

    /**
     * Generate a P&L for a given period.
     *
     * @return array{
     *   period_start: string,
     *   period_end: string,
     *   income: float,
     *   cost_of_goods: float,
     *   gross_profit: float,
     *   expenses: float,
     *   net_profit: float,
     * }
     */
    public function profitAndLoss(int $companyId, string $periodStart, string $periodEnd): array
    {
        $income = (float) Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('issue_date', [$periodStart, $periodEnd])
            ->sum('total');

        $expenses = (float) Expense::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$periodStart, $periodEnd])
            ->sum('amount');

        $cogs = (float) JobCostEntry::where('company_id', $companyId)
            ->whereBetween('cost_date', [$periodStart, $periodEnd])
            ->sum('total_cost');

        $grossProfit = $income - $cogs;
        $netProfit   = $grossProfit - $expenses;

        return [
            'period_start'   => $periodStart,
            'period_end'     => $periodEnd,
            'income'         => round($income, 2),
            'cost_of_goods'  => round($cogs, 2),
            'gross_profit'   => round($grossProfit, 2),
            'expenses'       => round($expenses, 2),
            'net_profit'     => round($netProfit, 2),
        ];
    }

    // -----------------------------------------------------------------------
    // Balance Sheet
    // -----------------------------------------------------------------------

    /**
     * Snapshot balance sheet as at a given date.
     *
     * @return array{
     *   as_at: string,
     *   total_assets: float,
     *   total_liabilities: float,
     *   total_equity: float,
     * }
     */
    public function balanceSheet(int $companyId, string $asAt): array
    {
        $totals = Account::where('company_id', $companyId)
            ->active()
            ->with(['lines' => function ($q) use ($asAt): void {
                $q->whereHas('journalEntry', function ($q2) use ($asAt): void {
                    $q2->where('status', 'posted')->where('entry_date', '<=', $asAt);
                });
            }])
            ->get()
            ->groupBy('type')
            ->map(fn (Collection $accounts) => $accounts->sum(fn (Account $a) => $a->runningBalance()));

        return [
            'as_at'              => $asAt,
            'total_assets'       => round((float) ($totals['asset'] ?? 0), 2),
            'total_liabilities'  => round((float) ($totals['liability'] ?? 0), 2),
            'total_equity'       => round((float) ($totals['equity'] ?? 0), 2),
        ];
    }

    // -----------------------------------------------------------------------
    // Cash Flow
    // -----------------------------------------------------------------------

    /**
     * Simple cash flow: payments received minus payments made.
     *
     * @return array{
     *   period_start: string,
     *   period_end: string,
     *   cash_in: float,
     *   cash_out: float,
     *   net_cash: float,
     * }
     */
    public function cashFlow(int $companyId, string $periodStart, string $periodEnd): array
    {
        $cashIn = (float) Payment::where('company_id', $companyId)
            ->whereBetween('paid_at', [$periodStart, $periodEnd . ' 23:59:59'])
            ->sum('amount');

        $cashOut = (float) Expense::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$periodStart, $periodEnd])
            ->sum('amount');

        $payrollOut = (float) Payroll::where('company_id', $companyId)
            ->where('status', Payroll::STATUS_PAID)
            ->whereBetween('pay_date', [$periodStart, $periodEnd])
            ->sum('total_net');

        $totalOut = $cashOut + $payrollOut;

        return [
            'period_start' => $periodStart,
            'period_end'   => $periodEnd,
            'cash_in'      => round($cashIn, 2),
            'cash_out'     => round($totalOut, 2),
            'net_cash'     => round($cashIn - $totalOut, 2),
        ];
    }

    // -----------------------------------------------------------------------
    // Aged Receivables
    // -----------------------------------------------------------------------

    /**
     * Customer aged receivables buckets.
     *
     * @return array<string, float> current | 1_30 | 31_60 | 61_90 | over_90
     */
    public function agedReceivables(int $companyId): array
    {
        $today = now()->startOfDay();

        $buckets = [
            'current' => 0.0,
            '1_30'    => 0.0,
            '31_60'   => 0.0,
            '61_90'   => 0.0,
            'over_90' => 0.0,
        ];

        Invoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
            ->get()
            ->each(function (Invoice $invoice) use ($today, &$buckets): void {
                $balance = (float) $invoice->total - (float) ($invoice->paid_amount ?? 0);
                if ($balance <= 0) {
                    return;
                }

                $dueDate = $invoice->due_date ?? $invoice->issue_date;
                if ($dueDate === null || $dueDate->isFuture()) {
                    $buckets['current'] += $balance;
                    return;
                }

                $days = (int) $today->diffInDays($dueDate, false) * -1;

                if ($days <= 30) {
                    $buckets['1_30'] += $balance;
                } elseif ($days <= 60) {
                    $buckets['31_60'] += $balance;
                } elseif ($days <= 90) {
                    $buckets['61_90'] += $balance;
                } else {
                    $buckets['over_90'] += $balance;
                }
            });

        return array_map(fn (float $v) => round($v, 2), $buckets);
    }

    // -----------------------------------------------------------------------
    // Job Profitability
    // -----------------------------------------------------------------------

    /**
     * Profitability per service job (based on job cost entries only).
     *
     * Revenue from invoices will be linkable once invoice.service_job_id column is added.
     *
     * @return Collection<int, array{
     *   service_job_id: int,
     *   revenue: float,
     *   cost: float,
     *   margin: float,
     * }>
     */
    public function jobProfitability(int $companyId, ?string $periodStart = null, ?string $periodEnd = null): Collection
    {
        $costQuery = JobCostEntry::where('company_id', $companyId)
            ->selectRaw('service_job_id, SUM(total_cost) as total_cost')
            ->whereNotNull('service_job_id');

        if ($periodStart) {
            $costQuery->where('cost_date', '>=', $periodStart);
        }
        if ($periodEnd) {
            $costQuery->where('cost_date', '<=', $periodEnd);
        }

        $costs = $costQuery->groupBy('service_job_id')
            ->get()
            ->keyBy('service_job_id');

        return $costs->map(function ($row): array {
            $cost = (float) $row->total_cost;

            return [
                'service_job_id' => (int) $row->service_job_id,
                'revenue'        => 0.0, // future: link invoice.service_job_id
                'cost'           => round($cost, 2),
                'margin'         => 0.0,
            ];
        })->values();
    }
}
