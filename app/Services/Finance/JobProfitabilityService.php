<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Events\Finance\JobFinancialSummaryUpdated;
use App\Events\Finance\UnprofitableJobDetected;
use App\Models\Finance\JobCostRecord;
use App\Models\Finance\JobFinancialSummary;
use App\Models\Finance\JobRevenueRecord;
use App\Models\Work\ServiceJob;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class JobProfitabilityService
{
    /**
     * Calculate and persist the financial summary for a job.
     */
    public function calculateSummary(ServiceJob $job): JobFinancialSummary
    {
        $totalCost    = (float) JobCostRecord::withoutGlobalScopes()->where('job_id', $job->id)->sum('total_cost');
        $totalRevenue = (float) JobRevenueRecord::withoutGlobalScopes()->where('job_id', $job->id)->sum('total_revenue');

        $labourCost    = (float) JobCostRecord::withoutGlobalScopes()->where('job_id', $job->id)->where('cost_type', 'labour')->sum('total_cost');
        $materialsCost = (float) JobCostRecord::withoutGlobalScopes()->where('job_id', $job->id)->where('cost_type', 'materials')->sum('total_cost');
        $travelCost    = (float) JobCostRecord::withoutGlobalScopes()->where('job_id', $job->id)->where('cost_type', 'travel')->sum('total_cost');

        $grossMargin = round($totalRevenue - $totalCost, 2);

        // If revenue = 0 and cost > 0, margin pct stored as 0 (not profitable)
        $grossMarginPct = $totalRevenue > 0
            ? round($grossMargin / $totalRevenue, 4)
            : 0.0;

        $isProfitable = $totalRevenue > 0 && $grossMargin > 0;

        /** @var JobFinancialSummary $summary */
        $summary = JobFinancialSummary::withoutGlobalScopes()->updateOrCreate(
            ['job_id' => $job->id],
            [
                'company_id'       => $job->company_id,
                'total_cost'       => $totalCost,
                'total_revenue'    => $totalRevenue,
                'gross_margin'     => $grossMargin,
                'gross_margin_pct' => $grossMarginPct,
                'labour_cost'      => $labourCost,
                'materials_cost'   => $materialsCost,
                'travel_cost'      => $travelCost,
                'is_profitable'    => $isProfitable,
                'calculated_at'    => now(),
            ]
        );

        JobFinancialSummaryUpdated::dispatch($summary);

        if (! $isProfitable) {
            UnprofitableJobDetected::dispatch($summary);
        }

        return $summary;
    }

    /**
     * Alias for calculateSummary.
     */
    public function refreshSummary(ServiceJob $job): JobFinancialSummary
    {
        return $this->calculateSummary($job);
    }

    /**
     * Check whether a job is profitable (calculates if no summary exists).
     */
    public function isJobProfitable(ServiceJob $job): bool
    {
        $summary = JobFinancialSummary::withoutGlobalScopes()
            ->where('job_id', $job->id)
            ->first();

        if ($summary === null) {
            $summary = $this->calculateSummary($job);
        }

        return (bool) $summary->is_profitable;
    }

    /**
     * Get jobs that are at risk (unprofitable or below the margin threshold).
     *
     * @return Collection<int, JobFinancialSummary>
     */
    public function getAtRiskJobs(int $companyId, float $marginThreshold = 0.0): Collection
    {
        return JobFinancialSummary::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($marginThreshold) {
                $query->where('is_profitable', false)
                    ->orWhere('gross_margin_pct', '<=', $marginThreshold);
            })
            ->get();
    }

    /**
     * Get unprofitable jobs calculated since a given date.
     *
     * @return Collection<int, JobFinancialSummary>
     */
    public function getUnprofitableJobs(int $companyId, Carbon $since): Collection
    {
        return JobFinancialSummary::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('is_profitable', false)
            ->where('calculated_at', '>=', $since)
            ->get();
    }
}
