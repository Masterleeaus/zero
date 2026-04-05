<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\JobCostAllocation;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBill;
use App\Models\Work\ServiceJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;

/**
 * ForecastingService
 *
 * Generates revenue, cost, margin, and cash-runway forecasts by
 * projecting historical trends forward.
 *
 * Inputs:
 *   historical revenue (paid invoices)
 *   historical expenses
 *   labor utilization (payrolls)
 *   recurring/scheduled jobs
 *   supplier liabilities
 *   payroll cadence
 *
 * Outputs:
 *   revenue_forecast
 *   cost_forecast
 *   margin_forecast
 *   cash_runway_estimate (days)
 *
 * Supports 30-day, 90-day, and 12-month projections.
 */
class ForecastingService
{
    /**
     * Generate a forecast for the given horizon.
     *
     * @param  int    $horizonDays  30 | 90 | 365
     * @return array<string, mixed>
     */
    public function generate(int $companyId, int $horizonDays = 90): array
    {
        $lookbackDays = max($horizonDays, 90);
        $from         = now()->subDays($lookbackDays)->startOfDay();
        $to           = now()->endOfDay();

        $historicalRevenue  = $this->historicalRevenue($companyId, $from, $to, $lookbackDays);
        $historicalCost     = $this->historicalCost($companyId, $from, $to, $lookbackDays);
        $historicalPayroll  = $this->historicalPayroll($companyId, $from, $to, $lookbackDays);
        $scheduledRevenue   = $this->scheduledRevenue($companyId, $horizonDays);
        $pendingLiabilities = $this->pendingLiabilities($companyId);

        $dailyRevenueTrend = $historicalRevenue / $lookbackDays;
        $dailyCostTrend    = ($historicalCost + $historicalPayroll) / $lookbackDays;

        $revenueForecast = round(($dailyRevenueTrend * $horizonDays) + $scheduledRevenue, 2);
        $costForecast    = round($dailyCostTrend * $horizonDays, 2);
        $marginForecast  = round($revenueForecast - $costForecast, 2);
        $marginPct       = $revenueForecast > 0
            ? round(($marginForecast / $revenueForecast) * 100, 2)
            : 0.0;

        $cashBalance    = $this->currentCashBalance($companyId);
        $netDailyBurn   = $dailyCostTrend - $dailyRevenueTrend;
        $cashRunwayDays = $netDailyBurn > 0 && $cashBalance > 0
            ? (int) floor($cashBalance / $netDailyBurn)
            : null;

        $forecast = [
            'company_id'              => $companyId,
            'generated_at'            => now()->toDateTimeString(),
            'horizon_days'            => $horizonDays,
            'historical_lookback_days'=> $lookbackDays,
            'revenue_forecast'        => $revenueForecast,
            'cost_forecast'           => $costForecast,
            'margin_forecast'         => $marginForecast,
            'margin_pct_forecast'     => $marginPct,
            'cash_runway_estimate'    => $cashRunwayDays,
            'pending_liabilities'     => round($pendingLiabilities, 2),
            'daily_revenue_trend'     => round($dailyRevenueTrend, 4),
            'daily_cost_trend'        => round($dailyCostTrend, 4),
        ];

        Event::dispatch(new \App\Events\Money\ForecastGenerated($companyId, $forecast));

        return $forecast;
    }

    /**
     * 30-day forecast shortcut.
     */
    public function forecast30(int $companyId): array
    {
        return $this->generate($companyId, 30);
    }

    /**
     * 90-day forecast shortcut.
     */
    public function forecast90(int $companyId): array
    {
        return $this->generate($companyId, 90);
    }

    /**
     * 12-month (365-day) forecast shortcut.
     */
    public function forecast12Month(int $companyId): array
    {
        return $this->generate($companyId, 365);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function historicalRevenue(int $companyId, Carbon $from, Carbon $to, int $days): float
    {
        return (float) Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total');
    }

    private function historicalCost(int $companyId, Carbon $from, Carbon $to, int $days): float
    {
        return (float) JobCostAllocation::where('company_id', $companyId)
            ->whereBetween('allocated_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    private function historicalPayroll(int $companyId, Carbon $from, Carbon $to, int $days): float
    {
        return (float) Payroll::where('company_id', $companyId)
            ->where('status', Payroll::STATUS_PAID)
            ->whereBetween('pay_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total_net');
    }

    private function scheduledRevenue(int $companyId, int $horizonDays): float
    {
        // Invoices already issued in the future horizon window (scheduled work)
        $to = now()->addDays($horizonDays)->toDateString();

        return (float) Invoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
            ->where('issue_date', '>', now()->toDateString())
            ->where('issue_date', '<=', $to)
            ->sum('total');
    }

    private function pendingLiabilities(int $companyId): float
    {
        $bills = (float) SupplierBill::where('company_id', $companyId)
            ->where('status', SupplierBill::STATUS_APPROVED)
            ->sum('total');

        $payrolls = (float) Payroll::where('company_id', $companyId)
            ->where('status', Payroll::STATUS_APPROVED)
            ->sum('total_net');

        return $bills + $payrolls;
    }

    private function currentCashBalance(int $companyId): float
    {
        return (float) \App\Models\Money\Payment::where('company_id', $companyId)
            ->sum('amount');
    }
}
