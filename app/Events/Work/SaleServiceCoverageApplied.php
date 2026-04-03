<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use App\Models\Work\ServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when service coverage is applied to an agreement as a result of a sale.
 *
 * Covers the transition: sold service → coverage active on agreement/plan.
 */
class SaleServiceCoverageApplied
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceAgreement $agreement,
        public readonly Quote $quote,
    ) {}
}
