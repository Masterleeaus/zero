<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use App\Models\Work\ServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a sale-backed recurring agreement is updated from a new sale/renewal.
 *
 * Covers: renewal quotes updating commercial terms, coverage window extension,
 * committed visit count changes driven by a new sale.
 */
class SaleRecurringAgreementUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceAgreement $agreement,
        public readonly Quote $renewalQuote,
    ) {}
}
