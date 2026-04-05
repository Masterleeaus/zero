<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Events\Money\BudgetThresholdExceeded;
use App\Events\Money\LiquidityRiskDetected;
use App\Events\Money\MarginErosionEscalated;
use App\Events\Money\PayablesPressureDetected;
use App\Models\Money\Budget;
use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBill;
use Illuminate\Support\Facades\Event;

/**
 * FinancialRiskEscalationService
 *
 * Detects and escalates financial risk conditions.
 */
class FinancialRiskEscalationService
{
    public function __construct(
        protected FinancialActionRecommendationService $recommendations,
        protected BudgetVarianceService $variance,
    ) {}

    public function evaluate(int $companyId, array $options = []): array
    {
        $risks = [];

        $liquidityRisk = $this->checkLiquidityRisk($companyId, $options['runway_threshold_days'] ?? 30);
        if ($liquidityRisk) $risks[] = $liquidityRisk;

        $payablesPressure = $this->checkPayablesPressure($companyId, $options['payables_threshold'] ?? 10000);
        if ($payablesPressure) $risks[] = $payablesPressure;

        $marginErosion = $this->checkMarginErosion($companyId, $options['min_margin_pct'] ?? 10.0);
        if ($marginErosion) $risks[] = $marginErosion;

        $budgetRisks = $this->checkBudgetOverruns($companyId, $options['overrun_threshold_pct'] ?? 15.0);
        $risks = array_merge($risks, $budgetRisks);

        return [
            'company_id'   => $companyId,
            'evaluated_at' => now()->toDateTimeString(),
            'risk_count'   => count($risks),
            'risks'        => $risks,
        ];
    }

    private function checkLiquidityRisk(int $companyId, int $runwayThresholdDays): ?array
    {
        $from = now()->subDays(90)->startOfDay();
        $to   = now()->endOfDay();

        $revenue = (float) Invoice::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('issue_date', [$from, $to])
            ->sum('total');

        $costs = (float) Expense::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');

        $labor = (float) Payroll::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereBetween('period_start', [$from, $to])
            ->sum('total_gross');

        $dailyBurn    = ($costs + $labor) / 90;
        $dailyRevenue = $revenue / 90;
        $netDailyBurn = $dailyBurn - $dailyRevenue;

        if ($netDailyBurn <= 0) return null;

        $cashBuffer = max($revenue * 0.1, 0);
        $runwayDays = $dailyBurn > 0 ? (int) floor($cashBuffer / $dailyBurn) : null;

        if ($runwayDays !== null && $runwayDays < $runwayThresholdDays) {
            $detail = ['runway_days' => $runwayDays, 'threshold' => $runwayThresholdDays, 'daily_burn' => round($dailyBurn, 2)];
            Event::dispatch(new LiquidityRiskDetected($companyId, $detail));
            $this->recommendations->recommend(
                companyId: $companyId,
                actionType: 'liquidity_risk',
                title: 'Low Cash Runway Detected',
                summary: "Cash runway estimated at {$runwayDays} days — below {$runwayThresholdDays} day threshold.",
                reason: 'Daily burn rate exceeds daily revenue inflow.',
                severity: $runwayDays < 14 ? 'critical' : 'high',
                confidence: 80.0,
                sourceService: 'FinancialRiskEscalationService',
                payload: $detail,
            );
            return ['type' => 'liquidity_risk', 'detail' => $detail];
        }
        return null;
    }

    private function checkPayablesPressure(int $companyId, float $threshold): ?array
    {
        $payables = (float) SupplierBill::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereIn('status', ['draft', 'approved'])
            ->sum('total_amount');

        if ($payables > $threshold) {
            $detail = ['payables_total' => round($payables, 2), 'threshold' => $threshold];
            Event::dispatch(new PayablesPressureDetected($companyId, $detail));
            $this->recommendations->recommend(
                companyId: $companyId,
                actionType: 'payables_pressure',
                title: 'High Supplier Payables Outstanding',
                summary: 'Outstanding payables of $' . number_format($payables, 2) . ' exceed threshold.',
                reason: 'Supplier liability has exceeded the warning threshold.',
                severity: 'high',
                confidence: 95.0,
                sourceService: 'FinancialRiskEscalationService',
                payload: $detail,
            );
            return ['type' => 'payables_pressure', 'detail' => $detail];
        }
        return null;
    }

    private function checkMarginErosion(int $companyId, float $minMarginPct): ?array
    {
        $from = now()->subDays(30)->startOfDay();
        $to   = now()->endOfDay();

        $revenue = (float) Invoice::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('issue_date', [$from, $to])
            ->sum('total');

        $costs = (float) Expense::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');

        if ($revenue <= 0) return null;

        $margin    = $revenue - $costs;
        $marginPct = round(($margin / $revenue) * 100, 2);

        if ($marginPct < $minMarginPct) {
            $detail = ['margin_pct' => $marginPct, 'threshold' => $minMarginPct, 'revenue' => round($revenue, 2), 'costs' => round($costs, 2)];
            Event::dispatch(new MarginErosionEscalated($companyId, $detail));
            $this->recommendations->recommend(
                companyId: $companyId,
                actionType: 'margin_erosion',
                title: 'Margin Erosion Detected',
                summary: "30-day margin is {$marginPct}% — below the {$minMarginPct}% threshold.",
                reason: 'Recent revenue-to-cost ratio has declined below acceptable threshold.',
                severity: $marginPct < 0 ? 'critical' : 'high',
                confidence: 85.0,
                sourceService: 'FinancialRiskEscalationService',
                payload: $detail,
            );
            return ['type' => 'margin_erosion', 'detail' => $detail];
        }
        return null;
    }

    private function checkBudgetOverruns(int $companyId, float $overrunThresholdPct): array
    {
        $risks = [];
        $activeBudgets = Budget::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        foreach ($activeBudgets as $budget) {
            $report = $this->variance->variance($budget->id);
            if (abs($report['total_variance_pct'] ?? 0) >= $overrunThresholdPct) {
                $detail = [
                    'budget_id'      => $budget->id,
                    'budget_name'    => $budget->name,
                    'variance_pct'   => $report['total_variance_pct'],
                    'total_budget'   => $report['total_budget'],
                    'total_actual'   => $report['total_actual'],
                    'total_variance' => $report['total_variance'],
                ];
                Event::dispatch(new BudgetThresholdExceeded($companyId, $detail));
                $this->recommendations->recommend(
                    companyId: $companyId,
                    actionType: 'budget_overrun',
                    title: 'Budget Overrun: ' . $budget->name,
                    summary: 'Budget variance of ' . $report['total_variance_pct'] . '% detected.',
                    reason: 'Budget ' . $budget->name . ' has exceeded its variance threshold.',
                    severity: abs($report['total_variance_pct'] ?? 0) > 30 ? 'critical' : 'high',
                    confidence: 90.0,
                    sourceService: 'FinancialRiskEscalationService',
                    payload: $detail,
                    relatedType: 'budget',
                    relatedId: $budget->id,
                );
                $risks[] = ['type' => 'budget_overrun', 'detail' => $detail];
            }
        }
        return $risks;
    }
}
