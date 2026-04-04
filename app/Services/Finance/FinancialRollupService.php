<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\Finance\FinancialRollup;
use App\Models\Finance\JobFinancialSummary;
use App\Models\Work\ServiceJob;
use Carbon\Carbon;

class FinancialRollupService
{
    /**
     * Build a rollup for a specific customer across their jobs.
     */
    public function rollupForCustomer(
        int $companyId,
        int $customerId,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
    ): FinancialRollup {
        return $this->buildRollup(
            $companyId,
            'customer',
            (string) $customerId,
            $periodStart,
            $periodEnd,
            fn ($query) => $query->where('customer_id', $customerId),
        );
    }

    /**
     * Build a rollup for a specific agreement.
     */
    public function rollupForAgreement(
        int $companyId,
        int $agreementId,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
    ): FinancialRollup {
        return $this->buildRollup(
            $companyId,
            'agreement',
            (string) $agreementId,
            $periodStart,
            $periodEnd,
            fn ($query) => $query->where('agreement_id', $agreementId),
        );
    }

    /**
     * Build a period-based rollup (e.g. monthly).
     */
    public function rollupForPeriod(
        int $companyId,
        Carbon $periodStart,
        Carbon $periodEnd,
        string $rollupType = 'month',
    ): FinancialRollup {
        $rollupKey = $periodStart->format('Y-m');

        return $this->buildRollup(
            $companyId,
            $rollupType,
            $rollupKey,
            $periodStart,
            $periodEnd,
            fn ($query) => $query->whereBetween('date_start', [$periodStart->toDateString(), $periodEnd->toDateString()]),
        );
    }

    /**
     * Refresh all rollup types for a company.
     */
    public function refreshAllRollups(int $companyId): void
    {
        // Customer rollups
        $customerIds = ServiceJob::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id');

        foreach ($customerIds as $customerId) {
            $this->rollupForCustomer($companyId, $customerId);
        }

        // Agreement rollups
        $agreementIds = ServiceJob::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereNotNull('agreement_id')
            ->distinct()
            ->pluck('agreement_id');

        foreach ($agreementIds as $agreementId) {
            $this->rollupForAgreement($companyId, $agreementId);
        }

        // Current month rollup
        $this->rollupForPeriod(
            $companyId,
            now()->startOfMonth(),
            now()->endOfMonth(),
            'month',
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * @param  callable(\Illuminate\Database\Eloquent\Builder): \Illuminate\Database\Eloquent\Builder  $jobFilter
     */
    private function buildRollup(
        int $companyId,
        string $rollupType,
        string $rollupKey,
        ?Carbon $periodStart,
        ?Carbon $periodEnd,
        callable $jobFilter,
    ): FinancialRollup {
        $jobQuery = ServiceJob::withoutGlobalScopes()
            ->where('company_id', $companyId);

        $jobQuery = $jobFilter($jobQuery);

        $jobIds = $jobQuery->pluck('id');

        $summaries = JobFinancialSummary::withoutGlobalScopes()
            ->whereIn('job_id', $jobIds)
            ->get();

        $totalCost    = round((float) $summaries->sum('total_cost'), 2);
        $totalRevenue = round((float) $summaries->sum('total_revenue'), 2);
        $grossMargin  = round($totalRevenue - $totalCost, 2);
        $marginPct    = $totalRevenue > 0 ? round($grossMargin / $totalRevenue, 4) : 0.0;

        /** @var FinancialRollup $rollup */
        $rollup = FinancialRollup::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id'   => $companyId,
                'rollup_type'  => $rollupType,
                'rollup_key'   => $rollupKey,
                'period_start' => $periodStart?->toDateString(),
                'period_end'   => $periodEnd?->toDateString(),
            ],
            [
                'job_count'        => $jobIds->count(),
                'total_cost'       => $totalCost,
                'total_revenue'    => $totalRevenue,
                'gross_margin'     => $grossMargin,
                'gross_margin_pct' => $marginPct,
                'calculated_at'    => now(),
            ]
        );

        return $rollup;
    }
}
