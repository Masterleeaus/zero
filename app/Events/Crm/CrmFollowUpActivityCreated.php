<?php

declare(strict_types=1);

namespace App\Events\Crm;

use App\Models\Work\JobActivity;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a CRM follow-up activity is created on a service job.
 *
 * Module 6 (fieldservice_crm) — crm_followup_activity_created signal.
 */
class CrmFollowUpActivityCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly JobActivity $activity,
    ) {}
}
