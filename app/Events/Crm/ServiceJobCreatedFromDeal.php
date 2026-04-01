<?php

declare(strict_types=1);

namespace App\Events\Crm;

use App\Models\Crm\Deal;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceJob is created from a CRM deal (opportunity).
 *
 * Module 6 (fieldservice_crm) — service_job_created_from_opportunity signal.
 */
class ServiceJobCreatedFromDeal
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly Deal $deal,
    ) {}
}
