<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\Payment;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBill;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * CashflowService
 *
 * Produces actual and projected cash flow data for weekly, monthly,
 * and rolling 90-day windows. All queries are company_id scoped.
 *
 * Sources:
 *   Actual inflow  – Payments (received)
 *   Actual outflow – Expenses (approved) + Payroll (paid)
 *   Projected in   – open Invoices due within window
 *   Projected out  – outstanding SupplierBills + approved Payrolls pending payment
 */
class CashflowService
{
    /**
     * Return actual + projected cash position for a date range.
     *
     * @return array{
     *   period_start: string,
     *   period_end: string,
     *   actual_inflow: float,
     *   actual_outflow: float,
     *   projected_inflow: float,
     *   projected_outflow: float,
     *   net_actual: float,
     *   net_projected: float,
     *   net_position: float,
     * }
     */
    public function forPeriod(int $companyId, Carbon $from, Carbon $to): array
    {
        $actualInflow = (float) Payment::where('company_id', $companyId)
            ->whereBetween('paid_at', [$from, $to->copy()->endOfDay()])
            ->sum('amount');

        $actualExpenses = (float) Expense::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $actualPayroll = (float) Payroll::where('company_id', $companyId)
            ->where('status', Payroll::STATUS_PAID)
            ->whereBetween('pay_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total_net');

        $actualOutflow = $actualExpenses + $actualPayroll;

        // Projected inflow = invoices due in the period, not yet paid
        $projectedInflow = (float) Invoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
            ->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
            ->sum('balance');

        // Projected outflow = supplier bills approved + pending payrolls
        $projectedSupplier = (float) SupplierBill::where('company_id', $companyId)
            ->where('status', SupplierBill::STATUS_APPROVED)
            ->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total');

        $projectedPayroll = (float) Payroll::where('company_id', $companyId)
            ->where('status', Payroll::STATUS_APPROVED)
            ->whereBetween('pay_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total_net');

        $projectedOutflow = $projectedSupplier + $projectedPayroll;

        return [
            'period_start'      => $from->toDateString(),
            'period_end'        => $to->toDateString(),
            'actual_inflow'     => round($actualInflow, 2),
            'actual_outflow'    => round($actualOutflow, 2),
            'projected_inflow'  => round($projectedInflow, 2),
            'projected_outflow' => round($projectedOutflow, 2),
            'net_actual'        => round($actualInflow - $actualOutflow, 2),
            'net_projected'     => round($projectedInflow - $projectedOutflow, 2),
            'net_position'      => round(($actualInflow + $projectedInflow) - ($actualOutflow + $projectedOutflow), 2),
        ];
    }

    /**
     * Weekly cash projection for the next N weeks from today.
     *
     * @return array<int, array{week_start: string, week_end: string, ...}>
     */
    public function weeklyProjection(int $companyId, int $weeks = 4): array
    {
        $result = [];
        $cursor = now()->startOfWeek();

        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = $cursor->copy();
            $weekEnd   = $cursor->copy()->endOfWeek();
            $result[]  = array_merge(
                ['week' => $i + 1],
                $this->forPeriod($companyId, $weekStart, $weekEnd)
            );
            $cursor->addWeek();
        }

        return $result;
    }

    /**
     * Monthly cash projection for the next N months from today.
     *
     * @return array<int, array{month: string, ...}>
     */
    public function monthlyProjection(int $companyId, int $months = 3): array
    {
        $result = [];
        $cursor = now()->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $monthStart = $cursor->copy();
            $monthEnd   = $cursor->copy()->endOfMonth();
            $data       = $this->forPeriod($companyId, $monthStart, $monthEnd);
            $data['month'] = $monthStart->format('Y-m');
            $result[]   = $data;
            $cursor->addMonth();
        }

        return $result;
    }

    /**
     * Rolling 90-day cash projection broken into weekly buckets.
     *
     * @return array{
     *   rolling_days: int,
     *   weeks: array<int, array>,
     *   totals: array,
     * }
     */
    public function rolling90Day(int $companyId): array
    {
        $weeks  = $this->weeklyProjection($companyId, 13); // ~91 days
        $totals = [
            'actual_inflow'     => 0.0,
            'actual_outflow'    => 0.0,
            'projected_inflow'  => 0.0,
            'projected_outflow' => 0.0,
            'net_position'      => 0.0,
        ];

        foreach ($weeks as $w) {
            $totals['actual_inflow']     += $w['actual_inflow'];
            $totals['actual_outflow']    += $w['actual_outflow'];
            $totals['projected_inflow']  += $w['projected_inflow'];
            $totals['projected_outflow'] += $w['projected_outflow'];
        }

        $totals['net_position'] = round(
            ($totals['actual_inflow'] + $totals['projected_inflow']) -
            ($totals['actual_outflow'] + $totals['projected_outflow']),
            2
        );

        foreach ($totals as $k => $v) {
            $totals[$k] = round((float) $v, 2);
        }

        return [
            'rolling_days' => 90,
            'weeks'        => $weeks,
            'totals'       => $totals,
        ];
    }
}
