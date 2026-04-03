<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use App\Models\Work\ServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceAgreement is extended via a new Quote/sale
 * (e.g. renewal quote accepted → agreement end date pushed forward).
 */
class FieldServiceAgreementSaleExtended
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceAgreement $agreement,
        public readonly Quote $quote,
    ) {}
}
