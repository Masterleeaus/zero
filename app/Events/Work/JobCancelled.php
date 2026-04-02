<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceJob is cancelled.
 *
 * Stage C — CRM pipeline reacts to job_cancelled signal.
 * Listeners may update deal/enquiry status and trigger follow-up activities.
 */
class JobCancelled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ServiceJob $job) {}
}
