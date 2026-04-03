<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceJob is given a scheduled_date_start for the first time.
 *
 * Module 9 (fieldservice_calendar) — calendar lifecycle signal.
 */
class ServiceJobScheduled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ServiceJob $job) {}
}
