<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Events\Finance\JobCostRecorded;
use App\Models\Finance\JobCostRecord;
use App\Models\User;
use App\Models\Work\ServiceJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JobCostingService
{
    /**
     * Record a labour cost for a technician on a job.
     */
    public function recordLabourCost(ServiceJob $job, User $tech, float $hours, float $hourlyRate): JobCostRecord
    {
        return DB::transaction(function () use ($job, $tech, $hours, $hourlyRate) {
            $total = round($hours * $hourlyRate, 2);

            $record = JobCostRecord::create([
                'company_id'  => $job->company_id,
                'job_id'      => $job->id,
                'cost_type'   => 'labour',
                'description' => "Labour: {$tech->name} ({$hours} hrs @ \${$hourlyRate}/hr)",
                'quantity'    => $hours,
                'unit_cost'   => $hourlyRate,
                'total_cost'  => $total,
                'recorded_by' => $tech->id,
                'cost_date'   => now()->toDateString(),
                'is_billable' => true,
            ]);

            JobCostRecorded::dispatch($record);

            return $record;
        });
    }

    /**
     * Record materials costs from an array of line items.
     *
     * Each item: ['description' => string, 'quantity' => float, 'unit_cost' => float]
     *
     * @param  array<int, array{description: string, quantity: float, unit_cost: float}>  $items
     * @return Collection<int, JobCostRecord>
     */
    public function recordMaterialsCost(ServiceJob $job, array $items): Collection
    {
        return DB::transaction(function () use ($job, $items) {
            $records = collect();

            foreach ($items as $item) {
                $quantity  = (float) ($item['quantity'] ?? 1);
                $unitCost  = (float) ($item['unit_cost'] ?? 0);
                $total     = round($quantity * $unitCost, 2);

                $record = JobCostRecord::create([
                    'company_id'  => $job->company_id,
                    'job_id'      => $job->id,
                    'cost_type'   => 'materials',
                    'description' => $item['description'] ?? 'Materials',
                    'quantity'    => $quantity,
                    'unit_cost'   => $unitCost,
                    'total_cost'  => $total,
                    'cost_date'   => now()->toDateString(),
                    'is_billable' => true,
                ]);

                JobCostRecorded::dispatch($record);
                $records->push($record);
            }

            return $records;
        });
    }

    /**
     * Record a travel cost for a job.
     */
    public function recordTravelCost(ServiceJob $job, float $distance, float $ratePerKm): JobCostRecord
    {
        return DB::transaction(function () use ($job, $distance, $ratePerKm) {
            $total = round($distance * $ratePerKm, 2);

            $record = JobCostRecord::create([
                'company_id'  => $job->company_id,
                'job_id'      => $job->id,
                'cost_type'   => 'travel',
                'description' => "Travel: {$distance} km @ \${$ratePerKm}/km",
                'quantity'    => $distance,
                'unit_cost'   => $ratePerKm,
                'total_cost'  => $total,
                'cost_date'   => now()->toDateString(),
                'is_billable' => false,
            ]);

            JobCostRecorded::dispatch($record);

            return $record;
        });
    }

    /**
     * Get the total cost for a job.
     */
    public function getTotalCost(ServiceJob $job): float
    {
        return (float) JobCostRecord::withoutGlobalScopes()
            ->where('job_id', $job->id)
            ->sum('total_cost');
    }

    /**
     * Get cost breakdown grouped by cost_type.
     *
     * @return array<string, array{total: float, count: int, records: Collection<int, JobCostRecord>}>
     */
    public function getCostBreakdown(ServiceJob $job): array
    {
        $records = JobCostRecord::withoutGlobalScopes()
            ->where('job_id', $job->id)
            ->get();

        $breakdown = [];

        foreach (JobCostRecord::COST_TYPES as $type) {
            $typeRecords = $records->where('cost_type', $type);
            $breakdown[$type] = [
                'total'   => round((float) $typeRecords->sum('total_cost'), 2),
                'count'   => $typeRecords->count(),
                'records' => $typeRecords->values(),
            ];
        }

        return $breakdown;
    }
}
