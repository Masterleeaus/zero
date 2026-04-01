<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\JobActivity;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a follow-up date/time is scheduled on a job activity.
 */
class ActivityFollowUpScheduled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly JobActivity $activity,
    ) {}
}
