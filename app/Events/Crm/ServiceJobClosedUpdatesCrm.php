<?php

declare(strict_types=1);

namespace App\Events\Crm;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceJob is closed and the linked CRM record should be updated.
 *
 * Module 6 (fieldservice_crm) — service_job_closed_updates_crm signal.
 * Listeners may update enquiry/deal status, add a note, or trigger follow-up.
 */
class ServiceJobClosedUpdatesCrm
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ServiceJob $job) {}
}
