<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a Quote is marked as creating field-service work
 * (i.e. at least one line has field_service_tracking != 'no').
 *
 * Mirrors Odoo fieldservice_sale: sale order confirmation with FSM-tracked lines.
 */
class FieldServiceSaleCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Quote $quote) {}
}
