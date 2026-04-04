<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Invoice;
use App\Models\Money\JobCostAllocation;
use App\Models\Work\ServiceJob;
use Carbon\Carbon;

class ProfitabilityService
{
    public function forJob(ServiceJob $job): array
    {
        $grossCost = (float) JobCostAllocation::query()
            ->forJob($job->id)
            ->sum('amount');

        $grossRevenue = $this->revenueForJob($job);

        return $this->calculateMargins($grossRevenue, $grossCost);
    }

    public function forSite(int $siteId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $costQuery = JobCostAllocation::query()->forSite($siteId);
        $this->applyDateFilter($costQuery, $from, $to);
        $grossCost = (float) $costQuery->sum('amount');

        $jobIds = $this->jobIdsForSite($siteId, $from, $to);
        $grossRevenue = $this->sumRevenueForJobs($jobIds);

        return $this->calculateMargins($grossRevenue, $grossCost);
    }

    public function forTeam(int $teamId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $costQuery = JobCostAllocation::query()->forTeam($teamId);
        $this->applyDateFilter($costQuery, $from, $to);
        $grossCost = (float) $costQuery->sum('amount');

        $jobIds = $this->jobIdsForTeam($teamId, $from, $to);
        $grossRevenue = $this->sumRevenueForJobs($jobIds);

        return $this->calculateMargins($grossRevenue, $grossCost);
    }

    public function forCustomer(int $customerId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $costQuery = JobCostAllocation::query()->forCustomer($customerId);
        $this->applyDateFilter($costQuery, $from, $to);
        $grossCost = (float) $costQuery->sum('amount');

        $jobIds = ServiceJob::query()
            ->where('customer_id', $customerId)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->pluck('id')
            ->toArray();

        $grossRevenue = $this->sumRevenueForJobs($jobIds);

        return $this->calculateMargins($grossRevenue, $grossCost);
    }

    public function forPeriod(int $companyId, Carbon $from, Carbon $to): array
    {
        $grossCost = (float) JobCostAllocation::query()
            ->where('company_id', $companyId)
            ->whereBetween('allocated_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $jobIds = ServiceJob::query()
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$from, $to])
            ->pluck('id')
            ->toArray();

        $grossRevenue = $this->sumRevenueForJobs($jobIds);

        return $this->calculateMargins($grossRevenue, $grossCost);
    }

    public function calculateMargins(float $revenue, float $cost): array
    {
        $grossMargin = $revenue - $cost;
        $marginPct   = $revenue > 0 ? round(($grossMargin / $revenue) * 100, 2) : 0.0;

        return [
            'gross_revenue' => round($revenue, 2),
            'gross_cost'    => round($cost, 2),
            'gross_margin'  => round($grossMargin, 2),
            'margin_pct'    => $marginPct,
        ];
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function revenueForJob(ServiceJob $job): float
    {
        if (isset($job->actual_revenue) && (float) $job->actual_revenue > 0) {
            return (float) $job->actual_revenue;
        }

        return (float) Invoice::query()
            ->where('service_job_id', $job->id)
            ->whereIn('status', ['paid', 'sent', 'approved'])
            ->sum('total');
    }

    private function sumRevenueForJobs(array $jobIds): float
    {
        if (empty($jobIds)) {
            return 0.0;
        }

        return (float) Invoice::query()
            ->whereIn('service_job_id', $jobIds)
            ->whereIn('status', ['paid', 'sent', 'approved'])
            ->sum('total');
    }

    private function jobIdsForSite(int $siteId, ?Carbon $from, ?Carbon $to): array
    {
        return ServiceJob::query()
            ->where('site_id', $siteId)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->pluck('id')
            ->toArray();
    }

    private function jobIdsForTeam(int $teamId, ?Carbon $from, ?Carbon $to): array
    {
        return ServiceJob::query()
            ->where('team_id', $teamId)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->pluck('id')
            ->toArray();
    }

    private function applyDateFilter(\Illuminate\Database\Eloquent\Builder $query, ?Carbon $from, ?Carbon $to): void
    {
        if ($from) {
            $query->whereDate('allocated_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('allocated_at', '<=', $to);
        }
    }
}
