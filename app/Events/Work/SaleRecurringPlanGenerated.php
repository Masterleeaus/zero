<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServicePlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServicePlan is generated from a sale-backed recurring agreement.
 *
 * Distinct from FieldServiceSaleConvertedToPlan (which covers any plan from a quote)
 * — this event specifically marks that the plan carries committed commercial terms
 * (visits_committed, commercial_start_date, commercial_end_date).
 */
class SaleRecurringPlanGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Quote $quote,
        public readonly ServiceAgreement $agreement,
        public readonly ServicePlan $plan,
    ) {}
}
