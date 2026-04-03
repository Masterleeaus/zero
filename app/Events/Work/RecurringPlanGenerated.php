<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use App\Models\Work\ServicePlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServicePlan is generated from a recurring sale.
 *
 * Mirrors Odoo fieldservice_sale_recurring:
 *   sale confirmation → service plan creation.
 */
class RecurringPlanGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServicePlan $plan,
        public readonly Quote $originQuote,
    ) {}
}
