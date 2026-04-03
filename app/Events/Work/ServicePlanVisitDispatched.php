<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlanVisit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServicePlanVisit is dispatched as a ServiceJob.
 *
 * Stage C/D — ServicePlanVisit dispatch lifecycle signal.
 */
class ServicePlanVisitDispatched
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServicePlanVisit $visit,
        public readonly ServiceJob $job,
    ) {}
}
