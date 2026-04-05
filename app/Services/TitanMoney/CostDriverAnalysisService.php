<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\JobCostAllocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * CostDriverAnalysisService
 *
 * Breaks down cost sources for a company (or job/period) into:
 *   labor %
 *   materials %
 *   supplier / subcontractor %
 *   overhead %
 *   transport %
 *   other %
 *
 * Maps JobCostAllocation.cost_type to one of the above buckets and
 * ranks contributors to margin erosion.
 */
class CostDriverAnalysisService
{
    /**
     * Cost-type → analysis bucket mapping.
     */
    private const BUCKET_MAP = [
        'labour'          => 'labor',
        'material'        => 'materials',
        'equipment'       => 'overhead',
        'subcontractor'   => 'supplier',
        'overhead'        => 'overhead',
        'reimbursable'    => 'other',
        'admin'           => 'overhead',
    ];

    /**
     * Full cost-driver breakdown for a company within an optional date window.
     *
     * @return array{
     *   totals: array<string, float>,
     *   percentages: array<string, float>,
     *   ranked_drivers: array<int, array{driver: string, amount: float, pct: float}>,
     *   total_cost: float,
     * }
     */
    public function breakdown(int $companyId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = JobCostAllocation::where('company_id', $companyId);

        if ($from) {
            $query->whereDate('allocated_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('allocated_at', '<=', $to);
        }

        $rows = $query->select('cost_type', DB::raw('SUM(amount) as total'))
            ->groupBy('cost_type')
            ->get();

        $buckets = [
            'labor'     => 0.0,
            'materials' => 0.0,
            'supplier'  => 0.0,
            'overhead'  => 0.0,
            'other'     => 0.0,
        ];

        foreach ($rows as $row) {
            $bucket = self::BUCKET_MAP[$row->cost_type] ?? 'other';
            $buckets[$bucket] += (float) $row->total;
        }

        $total = array_sum($buckets);

        $percentages = [];
        foreach ($buckets as $key => $amount) {
            $percentages[$key] = $total > 0 ? round(($amount / $total) * 100, 2) : 0.0;
        }

        // Rank drivers highest-to-lowest
        arsort($buckets);
        $ranked = [];
        foreach ($buckets as $driver => $amount) {
            $ranked[] = [
                'driver' => $driver,
                'amount' => round($amount, 2),
                'pct'    => $percentages[$driver],
            ];
        }

        return [
            'totals'         => array_map(fn (float $v) => round($v, 2), $buckets),
            'percentages'    => $percentages,
            'ranked_drivers' => $ranked,
            'total_cost'     => round($total, 2),
        ];
    }

    /**
     * Cost-driver breakdown for a single service job.
     *
     * @return array<string, mixed>
     */
    public function forJob(int $companyId, int $jobId): array
    {
        $query = JobCostAllocation::where('company_id', $companyId)
            ->where('service_job_id', $jobId);

        return $this->computeFromQuery($query);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return array<string, mixed>
     */
    private function computeFromQuery(\Illuminate\Database\Eloquent\Builder $query): array
    {
        $rows = $query->select('cost_type', DB::raw('SUM(amount) as total'))
            ->groupBy('cost_type')
            ->get();

        $buckets = [
            'labor'     => 0.0,
            'materials' => 0.0,
            'supplier'  => 0.0,
            'overhead'  => 0.0,
            'other'     => 0.0,
        ];

        foreach ($rows as $row) {
            $bucket = self::BUCKET_MAP[$row->cost_type] ?? 'other';
            $buckets[$bucket] += (float) $row->total;
        }

        $total = array_sum($buckets);
        $percentages = [];
        foreach ($buckets as $key => $amount) {
            $percentages[$key] = $total > 0 ? round(($amount / $total) * 100, 2) : 0.0;
        }

        arsort($buckets);
        $ranked = [];
        foreach ($buckets as $driver => $amount) {
            $ranked[] = [
                'driver' => $driver,
                'amount' => round($amount, 2),
                'pct'    => $percentages[$driver],
            ];
        }

        return [
            'totals'         => array_map(fn (float $v) => round($v, 2), $buckets),
            'percentages'    => $percentages,
            'ranked_drivers' => $ranked,
            'total_cost'     => round($total, 2),
        ];
    }
}
