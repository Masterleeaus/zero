<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServicePlanVisit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServicePlanVisit is marked as completed.
 *
 * Stage C — ServicePlanVisit lifecycle signal.
 */
class ServicePlanVisitCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ServicePlanVisit $visit) {}
}
