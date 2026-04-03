<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a recurring service sale is created from an accepted Quote.
 *
 * Mirrors Odoo fieldservice_sale_recurring:
 *   recurring product on sale order triggers recurring plan creation.
 */
class RecurringSaleCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Quote $quote) {}
}
