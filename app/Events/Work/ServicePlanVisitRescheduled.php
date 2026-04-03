<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServicePlanVisit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServicePlanVisit's scheduled_date or scheduled_for changes
 * after having already been set.
 *
 * Carries the previous scheduled date so consumers can update stale calendar entries.
 *
 * Module 9 (fieldservice_calendar) — calendar lifecycle signal.
 */
class ServicePlanVisitRescheduled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServicePlanVisit $visit,
        public readonly ?string $previousDate = null,
    ) {}
}
