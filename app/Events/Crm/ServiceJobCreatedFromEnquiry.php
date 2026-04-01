<?php

declare(strict_types=1);

namespace App\Events\Crm;

use App\Models\Crm\Enquiry;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceJob is created from a CRM enquiry (lead).
 *
 * Module 6 (fieldservice_crm) — service_job_created_from_lead signal.
 */
class ServiceJobCreatedFromEnquiry
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly Enquiry $enquiry,
    ) {}
}
