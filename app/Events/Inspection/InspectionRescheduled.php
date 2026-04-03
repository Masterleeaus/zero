<?php

declare(strict_types=1);

namespace App\Events\Inspection;

use App\Models\Inspection\InspectionInstance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an InspectionInstance's scheduled_at changes after having already been set.
 *
 * Carries the previous scheduled datetime so consumers can replace stale calendar entries.
 *
 * Module 9 (fieldservice_calendar) — calendar lifecycle signal.
 */
class InspectionRescheduled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly InspectionInstance $inspection,
        public readonly ?string $previousScheduledAt = null,
    ) {}
}
