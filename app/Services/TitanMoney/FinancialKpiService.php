<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\JobCostAllocation;
use App\Models\Money\Payment;
use App\Models\Money\Payroll;
use App\Models\Work\ServiceJob;
use Carbon\Carbon;

/**
 * FinancialKpiService
 *
 * Generates a set of financial key performance indicators for a company
 * across an optional date window.
 *
 * KPIs produced:
 *   gross_margin_pct    – gross margin percentage
 *   net_margin_pct      – net margin percentage
 *   labor_ratio         – labor cost / total cost
 *   material_ratio      – material cost / total cost
 *   revenue_per_team    – average revenue per team
 *   revenue_per_job     – average revenue per completed job
 *   cost_per_site       – average cost per site
 *   avg_job_profit      – average job profit (revenue - cost)
 *   cash_buffer_days    – days of operating expenses covered by cash on hand
 */
class FinancialKpiService
{
    /**
     * Compute all KPIs for the given company and optional period.
     *
     * @return array<string, float|int|null>
     */
    public function compute(int $companyId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from ??= now()->subDays(30)->startOfDay();
        $to   ??= now()->endOfDay();

        $totalRevenue = $this->totalRevenue($companyId, $from, $to);
        $totalCost    = $this->totalCost($companyId, $from, $to);
        $totalExpenses= $this->totalExpenses($companyId, $from, $to);
        $laborCost    = $this->costByType($companyId, $from, $to, 'labour');
        $materialCost = $this->costByType($companyId, $from, $to, 'material');
        $cashOnHand   = $this->cashOnHand($companyId);
        $dailyBurn    = $this->dailyBurn($companyId, $from, $to);
        $jobCount     = $this->jobCount($companyId, $from, $to);
        $teamCount    = $this->teamCount($companyId, $from, $to);
        $siteCount    = $this->siteCount($companyId, $from, $to);

        $grossMargin     = $totalRevenue - $totalCost;
        $netMargin       = $grossMargin - $totalExpenses;
        $grossMarginPct  = $totalRevenue > 0 ? round(($grossMargin / $totalRevenue) * 100, 2) : 0.0;
        $netMarginPct    = $totalRevenue > 0 ? round(($netMargin / $totalRevenue) * 100, 2) : 0.0;
        $laborRatio      = $totalCost > 0 ? round($laborCost / $totalCost, 4) : 0.0;
        $materialRatio   = $totalCost > 0 ? round($materialCost / $totalCost, 4) : 0.0;
        $revenuePerTeam  = $teamCount > 0 ? round($totalRevenue / $teamCount, 2) : null;
        $revenuePerJob   = $jobCount > 0 ? round($totalRevenue / $jobCount, 2) : null;
        $costPerSite     = $siteCount > 0 ? round($totalCost / $siteCount, 2) : null;
        $avgJobProfit    = $jobCount > 0 ? round($grossMargin / $jobCount, 2) : null;
        $cashBufferDays  = $dailyBurn > 0 ? (int) floor($cashOnHand / $dailyBurn) : null;

        return [
            'period_start'      => $from->toDateString(),
            'period_end'        => $to->toDateString(),
            'company_id'        => $companyId,
            'gross_margin_pct'  => $grossMarginPct,
            'net_margin_pct'    => $netMarginPct,
            'labor_ratio'       => $laborRatio,
            'material_ratio'    => $materialRatio,
            'revenue_per_team'  => $revenuePerTeam,
            'revenue_per_job'   => $revenuePerJob,
            'cost_per_site'     => $costPerSite,
            'avg_job_profit'    => $avgJobProfit,
            'cash_buffer_days'  => $cashBufferDays,
            'total_revenue'     => round($totalRevenue, 2),
            'total_cost'        => round($totalCost, 2),
            'total_expenses'    => round($totalExpenses, 2),
            'gross_margin'      => round($grossMargin, 2),
            'net_margin'        => round($netMargin, 2),
        ];
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function totalRevenue(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total');
    }

    private function totalCost(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) JobCostAllocation::where('company_id', $companyId)
            ->whereBetween('allocated_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    private function totalExpenses(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) Expense::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    private function costByType(int $companyId, Carbon $from, Carbon $to, string $costType): float
    {
        return (float) JobCostAllocation::where('company_id', $companyId)
            ->where('cost_type', $costType)
            ->whereBetween('allocated_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    private function cashOnHand(int $companyId): float
    {
        return (float) Payment::where('company_id', $companyId)->sum('amount');
    }

    private function dailyBurn(int $companyId, Carbon $from, Carbon $to): float
    {
        $days = max(1, (int) $from->diffInDays($to));
        $totalOut = $this->totalCost($companyId, $from, $to)
            + $this->totalExpenses($companyId, $from, $to);

        return $totalOut / $days;
    }

    private function jobCount(int $companyId, Carbon $from, Carbon $to): int
    {
        return (int) ServiceJob::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    private function teamCount(int $companyId, Carbon $from, Carbon $to): int
    {
        return (int) ServiceJob::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('team_id')
            ->distinct('team_id')
            ->count('team_id');
    }

    private function siteCount(int $companyId, Carbon $from, Carbon $to): int
    {
        return (int) JobCostAllocation::where('company_id', $companyId)
            ->whereBetween('allocated_at', [$from->toDateString(), $to->toDateString()])
            ->whereNotNull('site_id')
            ->distinct('site_id')
            ->count('site_id');
    }
}
