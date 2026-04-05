<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Budget;
use App\Models\Money\BudgetLine;
use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBill;
use Carbon\Carbon;

/**
 * BudgetVarianceService
 * Compares budgeted values against actuals from the ledger/costing layer.
 */
class BudgetVarianceService
{
    public function variance(int $budgetId): array
    {
        $budget = Budget::with('lines')->findOrFail($budgetId);
        $companyId = $budget->company_id;
        $from = Carbon::parse($budget->starts_at)->startOfDay();
        $to   = Carbon::parse($budget->ends_at)->endOfDay();

        $lines = [];
        $totalBudget = 0;
        $totalActual = 0;

        foreach ($budget->lines as $line) {
            $actual   = $this->resolveActual($companyId, $line, $from, $to);
            $budgeted = (float) $line->amount;
            $variance = $actual - $budgeted;
            $variancePct = $budgeted !== 0.0 ? round(($variance / abs($budgeted)) * 100, 2) : null;

            $lines[] = [
                'id'               => $line->id,
                'line_type'        => $line->line_type,
                'cost_bucket'      => $line->cost_bucket,
                'account_id'       => $line->account_id,
                'team_id'          => $line->team_id,
                'scenario_tag'     => $line->scenario_tag,
                'budget'           => $budgeted,
                'actual'           => $actual,
                'variance'         => round($variance, 2),
                'variance_percent' => $variancePct,
                'risk'             => $this->classifyRisk($line->line_type, $variancePct),
            ];

            $totalBudget += $budgeted;
            $totalActual += $actual;
        }

        $totalVariance    = round($totalActual - $totalBudget, 2);
        $totalVariancePct = $totalBudget !== 0.0 ? round(($totalVariance / abs($totalBudget)) * 100, 2) : null;

        return [
            'budget_id'          => $budget->id,
            'budget_name'        => $budget->name,
            'company_id'         => $companyId,
            'period'             => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'lines'              => $lines,
            'total_budget'       => round($totalBudget, 2),
            'total_actual'       => round($totalActual, 2),
            'total_variance'     => $totalVariance,
            'total_variance_pct' => $totalVariancePct,
            'generated_at'       => now()->toDateTimeString(),
        ];
    }

    public function companySummary(int $companyId): array
    {
        $budgets = Budget::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        $summaries = [];
        foreach ($budgets as $budget) {
            $summaries[] = $this->variance($budget->id);
        }

        return [
            'company_id'   => $companyId,
            'budget_count' => count($summaries),
            'budgets'      => $summaries,
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    private function resolveActual(int $companyId, BudgetLine $line, Carbon $from, Carbon $to): float
    {
        return match ($line->line_type) {
            'revenue'   => $this->actualRevenue($companyId, $from, $to),
            'labor'     => $this->actualLabor($companyId, $from, $to),
            'materials' => $this->actualMaterials($companyId, $from, $to),
            default     => $this->actualExpenses($companyId, $line->line_type, $from, $to),
        };
    }

    private function actualRevenue(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) Invoice::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('issue_date', [$from, $to])
            ->sum('total');
    }

    private function actualLabor(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) Payroll::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereBetween('period_start', [$from, $to])
            ->sum('total_gross');
    }

    private function actualMaterials(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) SupplierBill::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereIn('status', ['approved', 'paid', 'draft'])
            ->whereBetween('bill_date', [$from, $to])
            ->sum('total_amount');
    }

    private function actualExpenses(int $companyId, string $lineType, Carbon $from, Carbon $to): float
    {
        return (float) Expense::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');
    }

    private function classifyRisk(?string $lineType, ?float $variancePct): string
    {
        if ($variancePct === null) {
            return 'unknown';
        }
        $absVariance = abs($variancePct);
        $isExpense   = in_array($lineType, ['expense', 'labor', 'materials', 'overhead', 'capex', 'liability']);
        $isBad       = $isExpense ? $variancePct > 0 : $variancePct < 0;

        if (!$isBad) return 'on_track';
        if ($absVariance < 5)  return 'low';
        if ($absVariance < 15) return 'medium';
        if ($absVariance < 30) return 'high';
        return 'critical';
    }
}
