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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

/**
 * FinancialSnapshotService
 *
 * Produces a real-time financial health snapshot scoped per company,
 * with optional period or rolling window filtering.
 *
 * Outputs:
 *   cash_on_hand               – total received payments not yet offset
 *   receivables_total          – outstanding invoice balances
 *   payables_total             – outstanding supplier bills
 *   wages_liability_estimate   – approved-but-unpaid payroll total
 *   supplier_liability         – approved unpaid supplier bills
 *   job_cost_outstanding       – unposted job cost allocations
 *   unbilled_work_estimate     – invoiced revenue minus paid amount
 *   gross_margin_estimate      – estimated revenue minus total cost
 */
class FinancialSnapshotService
{
    /**
     * Full snapshot for a company.
     *
     * @return array<string, float|string>
     */
    public function snapshot(int $companyId, ?Carbon $asAt = null): array
    {
        $asAt ??= now();

        $cashOnHand = (float) Payment::where('company_id', $companyId)
            ->where('paid_at', '<=', $asAt)
            ->sum('amount');

        $receivablesTotal = (float) Invoice::where('company_id', $companyId)
            ->whereNotIn('status', ['draft', 'cancelled', 'paid'])
            ->where('issue_date', '<=', $asAt->toDateString())
            ->sum('balance');

        $payablesTotal = (float) SupplierBill::where('company_id', $companyId)
            ->where('status', SupplierBill::STATUS_APPROVED)
            ->where('bill_date', '<=', $asAt->toDateString())
            ->sum('total');

        $wagesLiability = (float) Payroll::where('company_id', $companyId)
            ->where('status', Payroll::STATUS_APPROVED)
            ->where('pay_date', '<=', $asAt->toDateString())
            ->sum('total_net');

        $supplierLiability = $payablesTotal;

        $jobCostOutstanding = (float) JobCostAllocation::where('company_id', $companyId)
            ->where('posted', false)
            ->where('allocated_at', '<=', $asAt->toDateString())
            ->sum('amount');

        $unbilledWork = (float) Invoice::where('company_id', $companyId)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->where('issue_date', '<=', $asAt->toDateString())
            ->sum(DB::raw('total - COALESCE(paid_amount, 0)'));

        $totalCost = (float) JobCostAllocation::where('company_id', $companyId)
            ->where('allocated_at', '<=', $asAt->toDateString())
            ->sum('amount');

        $totalRevenue = (float) Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('issue_date', '<=', $asAt->toDateString())
            ->sum('total');

        $grossMarginEstimate = $totalRevenue - $totalCost;

        $snapshot = [
            'as_at'                    => $asAt->toDateTimeString(),
            'company_id'               => $companyId,
            'cash_on_hand'             => round($cashOnHand, 2),
            'receivables_total'        => round($receivablesTotal, 2),
            'payables_total'           => round($payablesTotal, 2),
            'wages_liability_estimate' => round($wagesLiability, 2),
            'supplier_liability'       => round($supplierLiability, 2),
            'job_cost_outstanding'     => round($jobCostOutstanding, 2),
            'unbilled_work_estimate'   => round($unbilledWork, 2),
            'gross_margin_estimate'    => round($grossMarginEstimate, 2),
        ];

        Event::dispatch(new \App\Events\Money\FinancialSnapshotUpdated($companyId, $snapshot));

        return $snapshot;
    }

    /**
     * Period-bounded snapshot.
     *
     * @return array<string, float|string>
     */
    public function periodSnapshot(int $companyId, Carbon $from, Carbon $to): array
    {
        $receivablesTotal = (float) Invoice::where('company_id', $companyId)
            ->whereNotIn('status', ['draft', 'cancelled', 'paid'])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->sum('balance');

        $payablesTotal = (float) SupplierBill::where('company_id', $companyId)
            ->where('status', SupplierBill::STATUS_APPROVED)
            ->whereBetween('bill_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total');

        $totalCost = (float) JobCostAllocation::where('company_id', $companyId)
            ->whereBetween('allocated_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $totalRevenue = (float) Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total');

        $cashIn = (float) Payment::where('company_id', $companyId)
            ->whereBetween('paid_at', [$from, $to->endOfDay()])
            ->sum('amount');

        $cashOut = (float) Expense::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        return [
            'period_start'          => $from->toDateString(),
            'period_end'            => $to->toDateString(),
            'company_id'            => $companyId,
            'receivables_total'     => round($receivablesTotal, 2),
            'payables_total'        => round($payablesTotal, 2),
            'total_cost'            => round($totalCost, 2),
            'total_revenue'         => round($totalRevenue, 2),
            'gross_margin_estimate' => round($totalRevenue - $totalCost, 2),
            'cash_in'               => round($cashIn, 2),
            'cash_out'              => round($cashOut, 2),
            'net_cash'              => round($cashIn - $cashOut, 2),
        ];
    }

    /**
     * Rolling N-day snapshot.
     *
     * @return array<string, float|string>
     */
    public function rollingSnapshot(int $companyId, int $days = 30): array
    {
        return $this->periodSnapshot(
            $companyId,
            now()->subDays($days)->startOfDay(),
            now()->endOfDay()
        );
    }
}
