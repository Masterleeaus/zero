<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBill;
use Carbon\Carbon;

/**
 * ScenarioSimulationService
 *
 * Payload-driven what-if simulation. Does NOT mutate production data.
 */
class ScenarioSimulationService
{
    public function simulate(int $companyId, string $scenarioType, array $parameters = [], int $horizonDays = 90): array
    {
        $baseline = $this->baseline($companyId, $horizonDays);
        $adjusted = $this->applyScenario($baseline, $scenarioType, $parameters, $horizonDays);

        return [
            'company_id'    => $companyId,
            'scenario_type' => $scenarioType,
            'parameters'    => $parameters,
            'horizon_days'  => $horizonDays,
            'simulated_at'  => now()->toDateTimeString(),
            'baseline'      => $baseline,
            'adjusted'      => $adjusted,
            'impact'        => $this->computeImpact($baseline, $adjusted),
        ];
    }

    public function compareScenarios(int $companyId, array $scenarios, int $horizonDays = 90): array
    {
        $baseline = $this->baseline($companyId, $horizonDays);
        $results  = [];

        foreach ($scenarios as $scenario) {
            $type     = $scenario['type'] ?? 'unknown';
            $params   = $scenario['parameters'] ?? [];
            $adjusted = $this->applyScenario($baseline, $type, $params, $horizonDays);
            $results[] = [
                'scenario_type' => $type,
                'parameters'    => $params,
                'adjusted'      => $adjusted,
                'impact'        => $this->computeImpact($baseline, $adjusted),
            ];
        }

        return [
            'company_id'   => $companyId,
            'horizon_days' => $horizonDays,
            'simulated_at' => now()->toDateTimeString(),
            'baseline'     => $baseline,
            'scenarios'    => $results,
        ];
    }

    private function baseline(int $companyId, int $horizonDays): array
    {
        $lookback = max($horizonDays, 90);
        $from = now()->subDays($lookback)->startOfDay();
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

        $payables = (float) SupplierBill::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereIn('status', ['draft', 'approved'])
            ->sum('total_amount');

        $receivables = (float) Invoice::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('balance');

        $dailyRevenue = $revenue / max($lookback, 1);
        $dailyCost    = ($costs + $labor) / max($lookback, 1);

        $projectedRevenue = round($dailyRevenue * $horizonDays, 2);
        $projectedCosts   = round($dailyCost * $horizonDays, 2);
        $projectedLabor   = round(($labor / max($lookback, 1)) * $horizonDays, 2);
        $projectedMargin  = round($projectedRevenue - $projectedCosts, 2);
        $marginPct        = $projectedRevenue > 0 ? round($projectedMargin / $projectedRevenue * 100, 2) : 0.0;

        return [
            'projected_revenue'   => $projectedRevenue,
            'projected_costs'     => $projectedCosts,
            'projected_labor'     => $projectedLabor,
            'projected_margin'    => $projectedMargin,
            'margin_pct'          => $marginPct,
            'payables_pressure'   => round($payables, 2),
            'receivables_at_risk' => round($receivables, 2),
            'cash_runway_days'    => $this->estimateCashRunway($projectedRevenue, $projectedCosts, $horizonDays, $payables),
        ];
    }

    private function applyScenario(array $baseline, string $scenarioType, array $params, int $horizonDays): array
    {
        $adjusted = $baseline;

        switch ($scenarioType) {
            case 'supplier_price_increase':
                $pct = ($params['increase_pct'] ?? 10) / 100;
                $adjusted['projected_costs'] = round($baseline['projected_costs'] * (1 + $pct), 2);
                break;
            case 'labor_rate_increase':
                $pct = ($params['increase_pct'] ?? 10) / 100;
                $adjusted['projected_labor'] = round($baseline['projected_labor'] * (1 + $pct), 2);
                $adjusted['projected_costs'] = round(($baseline['projected_costs'] - $baseline['projected_labor']) + $adjusted['projected_labor'], 2);
                break;
            case 'staff_shortage':
                $reductionPct = ($params['reduction_pct'] ?? 20) / 100;
                $overtimePct  = ($params['overtime_pct'] ?? 15) / 100;
                $adjusted['projected_revenue'] = round($baseline['projected_revenue'] * (1 - $reductionPct), 2);
                $adjusted['projected_labor']   = round($baseline['projected_labor'] * (1 + $overtimePct), 2);
                $adjusted['projected_costs']   = round(($baseline['projected_costs'] - $baseline['projected_labor']) + $adjusted['projected_labor'], 2);
                break;
            case 'lower_utilization':
                $utilizationDrop = ($params['drop_pct'] ?? 15) / 100;
                $adjusted['projected_revenue'] = round($baseline['projected_revenue'] * (1 - $utilizationDrop), 2);
                break;
            case 'new_recurring_jobs':
                $additionalRevenue = (float) ($params['additional_revenue'] ?? 0);
                $additionalCost    = (float) ($params['additional_cost'] ?? $additionalRevenue * 0.6);
                $adjusted['projected_revenue'] = round($baseline['projected_revenue'] + $additionalRevenue, 2);
                $adjusted['projected_costs']   = round($baseline['projected_costs'] + $additionalCost, 2);
                break;
            case 'customer_churn':
                $churnPct = ($params['churn_pct'] ?? 10) / 100;
                $adjusted['projected_revenue'] = round($baseline['projected_revenue'] * (1 - $churnPct), 2);
                break;
            case 'fuel_cost_spike':
                $spikePct  = ($params['spike_pct'] ?? 20) / 100;
                $fuelShare = (float) ($params['fuel_cost_share'] ?? 0.05);
                $fuelCost  = $baseline['projected_costs'] * $fuelShare;
                $adjusted['projected_costs'] = round($baseline['projected_costs'] + ($fuelCost * $spikePct), 2);
                break;
            case 'delayed_collections':
                $delayPct = ($params['delay_pct'] ?? 30) / 100;
                $adjusted['receivables_at_risk'] = round($baseline['receivables_at_risk'] * (1 + $delayPct), 2);
                $adjusted['projected_revenue']   = round($baseline['projected_revenue'] * (1 - $delayPct * 0.5), 2);
                break;
            case 'reduced_scheduling':
                $densityDrop = ($params['density_drop_pct'] ?? 20) / 100;
                $adjusted['projected_revenue'] = round($baseline['projected_revenue'] * (1 - $densityDrop), 2);
                break;
            case 'reorder_timing_change':
                $stockupPct = ($params['stockup_pct'] ?? 25) / 100;
                $adjusted['payables_pressure'] = round($baseline['payables_pressure'] * (1 + $stockupPct), 2);
                $adjusted['projected_costs']   = round($baseline['projected_costs'] * (1 + $stockupPct * 0.1), 2);
                break;
        }

        $adjusted['projected_margin'] = round($adjusted['projected_revenue'] - $adjusted['projected_costs'], 2);
        $adjusted['margin_pct']        = $adjusted['projected_revenue'] > 0
            ? round($adjusted['projected_margin'] / $adjusted['projected_revenue'] * 100, 2)
            : 0.0;
        $adjusted['cash_runway_days']  = $this->estimateCashRunway(
            $adjusted['projected_revenue'],
            $adjusted['projected_costs'],
            $horizonDays,
            $adjusted['payables_pressure'],
        );

        return $adjusted;
    }

    private function computeImpact(array $baseline, array $adjusted): array
    {
        $revenueImpact  = round($adjusted['projected_revenue'] - $baseline['projected_revenue'], 2);
        $costImpact     = round($adjusted['projected_costs'] - $baseline['projected_costs'], 2);
        $marginImpact   = round($adjusted['projected_margin'] - $baseline['projected_margin'], 2);
        $marginPctDelta = round($adjusted['margin_pct'] - $baseline['margin_pct'], 2);

        return [
            'revenue_impact'    => $revenueImpact,
            'cost_impact'       => $costImpact,
            'margin_impact'     => $marginImpact,
            'margin_pct_delta'  => $marginPctDelta,
            'cash_runway_delta' => $adjusted['cash_runway_days'] !== null && $baseline['cash_runway_days'] !== null
                ? $adjusted['cash_runway_days'] - $baseline['cash_runway_days']
                : null,
            'severity'          => $this->impactSeverity($marginImpact, $baseline['projected_margin']),
        ];
    }

    private function estimateCashRunway(float $revenue, float $costs, int $horizonDays, float $liabilities): ?int
    {
        $netBurn = $costs - $revenue;
        if ($netBurn <= 0) {
            return null;
        }
        $cashBuffer = max($revenue * 0.1, 0);
        return (int) floor($cashBuffer / ($netBurn / $horizonDays));
    }

    private function impactSeverity(float $marginImpact, float $baselineMargin): string
    {
        if ($baselineMargin == 0) return 'unknown';
        $impactPct = abs($marginImpact / $baselineMargin) * 100;
        if ($impactPct < 5)  return 'low';
        if ($impactPct < 15) return 'medium';
        if ($impactPct < 30) return 'high';
        return 'critical';
    }
}
