<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a Quote transitions to accepted/approved status and is
 * eligible for field-service job/plan generation.
 *
 * Mirrors Odoo fieldservice_sale: _action_confirm on sale.order.
 */
class FieldServiceSaleApproved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Quote $quote) {}
}
