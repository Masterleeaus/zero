<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use App\Models\Work\ServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a Quote with agreement-sale tracking is accepted,
 * creating a new ServiceAgreement.
 *
 * Mirrors Odoo fieldservice_sale_agreement: agreement_id propagated to fsm.order.
 */
class FieldServiceAgreementSaleCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Quote $quote,
        public readonly ServiceAgreement $agreement,
    ) {}
}
