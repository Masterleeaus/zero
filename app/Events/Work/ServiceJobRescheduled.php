<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceJob's scheduled_date_start or scheduled_date_end changes
 * after having already been set.
 *
 * Carries the previous start so consumers can remove/replace stale calendar entries.
 *
 * Module 9 (fieldservice_calendar) — calendar lifecycle signal.
 */
class ServiceJobRescheduled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly ?string $previousStart = null,
    ) {}
}
