<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServicePlanVisit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServicePlanVisit is scheduled (a ServiceJob is generated).
 *
 * Stage C — ServicePlan lifecycle signal.
 */
class ServicePlanVisitScheduled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ServicePlanVisit $visit) {}
}
