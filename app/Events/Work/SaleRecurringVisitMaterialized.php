<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlanVisit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a projected sale-recurring visit is materialized as a ServiceJob.
 *
 * Covers the moment: pending ServicePlanVisit → dispatched ServiceJob,
 * specifically when the visit originated from a sale-backed recurring plan.
 */
class SaleRecurringVisitMaterialized
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServicePlanVisit $visit,
        public readonly ServiceJob $job,
    ) {}
}
