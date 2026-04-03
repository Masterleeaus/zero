<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlanVisit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a recurring ServicePlanVisit is materialized into a concrete ServiceJob.
 *
 * Mirrors Odoo fieldservice_sale_recurring execution pipeline:
 *   recurring visit pending → service job materialized.
 */
class RecurringVisitMaterialized
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServicePlanVisit $visit,
        public readonly ServiceJob $job,
    ) {}
}
