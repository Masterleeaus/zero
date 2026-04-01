<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\User;
use App\Models\Work\JobActivity;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a job activity is marked as done.
 */
class ActivityCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly JobActivity $activity,
        public readonly User $completedBy,
    ) {}
}
