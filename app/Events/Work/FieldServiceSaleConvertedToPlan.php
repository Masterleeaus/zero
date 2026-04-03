<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use App\Models\Work\ServicePlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a Quote acceptance results in a ServicePlan being generated
 * (recurring service sold through a quote).
 */
class FieldServiceSaleConvertedToPlan
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Quote $quote,
        public readonly ServicePlan $plan,
    ) {}
}
