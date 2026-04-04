<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\Finance\JobRevenueRecord;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JobRevenueService
{
    /**
     * Record revenue from an agreement (contract allocation).
     */
    public function recordFromAgreement(ServiceJob $job, ServiceAgreement $agreement): JobRevenueRecord
    {
        return DB::transaction(function () use ($job, $agreement) {
            return JobRevenueRecord::create([
                'company_id'     => $job->company_id,
                'job_id'         => $job->id,
                'revenue_type'   => 'contract_allocation',
                'description'    => "Agreement service allocation (Agreement #{$agreement->id})",
                'quantity'       => 1.000,
                'unit_price'     => 0.0000,
                'total_revenue'  => 0.00,
                'billing_source' => 'agreement',
                'agreement_id'   => $agreement->id,
                'is_invoiced'    => false,
            ]);
        });
    }

    /**
     * Record ad-hoc revenue from line items.
     *
     * Each item: ['revenue_type' => string, 'description' => string, 'quantity' => float, 'unit_price' => float]
     *
     * @param  array<int, array{revenue_type: string, description: string, quantity: float, unit_price: float}>  $lineItems
     * @return Collection<int, JobRevenueRecord>
     */
    public function recordAdHocRevenue(ServiceJob $job, array $lineItems): Collection
    {
        return DB::transaction(function () use ($job, $lineItems) {
            $records = collect();

            foreach ($lineItems as $item) {
                $quantity  = (float) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $total     = round($quantity * $unitPrice, 2);

                $record = JobRevenueRecord::create([
                    'company_id'     => $job->company_id,
                    'job_id'         => $job->id,
                    'revenue_type'   => $item['revenue_type'] ?? 'other',
                    'description'    => $item['description'] ?? '',
                    'quantity'       => $quantity,
                    'unit_price'     => $unitPrice,
                    'total_revenue'  => $total,
                    'billing_source' => 'ad_hoc',
                    'is_invoiced'    => false,
                ]);

                $records->push($record);
            }

            return $records;
        });
    }

    /**
     * Get the total revenue for a job.
     */
    public function getTotalRevenue(ServiceJob $job): float
    {
        return (float) JobRevenueRecord::withoutGlobalScopes()
            ->where('job_id', $job->id)
            ->sum('total_revenue');
    }
}
