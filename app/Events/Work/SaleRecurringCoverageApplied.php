<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServicePlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when recurring service coverage is applied from a sale-backed agreement.
 *
 * Distinct from SaleServiceCoverageApplied (which fires on any sale coverage):
 * this event specifically marks that recurring visit coverage is now attached
 * (i.e., a ServicePlan with committed visit counts has been linked).
 */
class SaleRecurringCoverageApplied
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceAgreement $agreement,
        public readonly ServicePlan $plan,
    ) {}
}
