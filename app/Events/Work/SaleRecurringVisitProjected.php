<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServicePlan;
use App\Models\Work\ServicePlanVisit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServicePlanVisit is projected (created as pending) from a
 * sale-backed recurring plan.
 *
 * At projection time the visit is pending — not yet dispatched as a ServiceJob.
 * Use SaleRecurringVisitMaterialized when the visit is dispatched as a job.
 */
class SaleRecurringVisitProjected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServicePlanVisit $visit,
        public readonly ServicePlan $plan,
    ) {}
}
