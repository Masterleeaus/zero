<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use App\Models\Work\ServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a sale-backed recurring service agreement is created.
 *
 * Covers the fieldservice_sale_recurring_agreement module:
 * a quote/sale approval creates a recurring-type agreement with
 * committed visit counts and commercial coverage dates.
 */
class SaleRecurringAgreementCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Quote $quote,
        public readonly ServiceAgreement $agreement,
    ) {}
}
