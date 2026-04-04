<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\JobCostAllocation;
use App\Models\Money\SupplierBill;
use App\Models\Money\SupplierBillLine;
use App\Models\Work\ServiceJob;
use Carbon\Carbon;

class MaterialCostingService
{
    public function __construct(protected JobCostingService $jobCosting) {}

    public function costFromSupplierBillLine(SupplierBillLine $line): array
    {
        $qty       = (float) ($line->quantity ?? 1);
        $unitPrice = (float) ($line->unit_price ?? 0);
        $amount    = (float) ($line->amount ?? round($qty * $unitPrice, 2));

        return [
            'description' => $line->description,
            'quantity'    => $qty,
            'unit_cost'   => $unitPrice,
            'amount'      => $amount,
            'supplier_id' => $line->bill?->supplier_id,
        ];
    }

    public function allocateBillLinesToJob(SupplierBill $bill, int $serviceJobId): array
    {
        $allocations = [];

        foreach ($bill->lines as $line) {
            $allocations[] = $this->jobCosting->allocateSupplierBillLine($line, $serviceJobId);
        }

        return $allocations;
    }

    public function allocateInventoryUsage(array $usageData, int $serviceJobId, int $companyId, int $createdBy): JobCostAllocation
    {
        return $this->jobCosting->allocateManual(
            array_merge($usageData, [
                'service_job_id' => $serviceJobId,
                'source_type'    => 'inventory_usage',
                'cost_type'      => 'material',
                'allocated_at'   => $usageData['allocated_at'] ?? Carbon::today()->toDateString(),
            ]),
            $companyId,
            $createdBy
        );
    }

    public function materialCostForJob(ServiceJob $job): float
    {
        return (float) JobCostAllocation::query()
            ->forJob($job->id)
            ->byCostType('material')
            ->sum('amount');
    }

    public function supplierCostForJob(ServiceJob $job): float
    {
        return (float) JobCostAllocation::query()
            ->forJob($job->id)
            ->byCostType('subcontractor')
            ->sum('amount');
    }
}
