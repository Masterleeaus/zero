<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use App\Models\Work\ServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceAgreement is activated by an approved Quote.
 *
 * This happens when the sale/quote approval triggers an existing
 * agreement to become active, or when a new agreement is created
 * and immediately activated from a sale.
 */
class FieldServiceAgreementSaleActivated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceAgreement $agreement,
        public readonly ?Quote $quote = null,
    ) {}
}
