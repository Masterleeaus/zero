<?php

declare(strict_types=1);

namespace App\Listeners\Finance;

use App\Events\Finance\JobInvoiced;
use App\Models\Finance\JobRevenueRecord;

class RecordRevenueOnJobBilled
{
    public function handle(JobInvoiced $event): void
    {
        $job     = $event->job;
        $invoice = $event->invoice;

        JobRevenueRecord::create([
            'company_id'     => $job->company_id,
            'job_id'         => $job->id,
            'revenue_type'   => 'labour',
            'description'    => "Invoice #{$invoice->invoice_number} — {$job->title}",
            'quantity'       => 1.000,
            'unit_price'     => (float) $invoice->total,
            'total_revenue'  => (float) $invoice->total,
            'billing_source' => 'ad_hoc',
            'is_invoiced'    => true,
            'invoiced_at'    => now(),
        ]);
    }
}
