<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\FieldServiceAgreementSaleActivated;

/**
 * On FieldServiceAgreementSaleActivated:
 *   → stub listener; extend with notification, CRM update, or scheduling
 *   surface sync as needed.
 */
class FieldServiceAgreementSaleActivatedListener
{
    public function handle(FieldServiceAgreementSaleActivated $event): void
    {
        // Future: notify customer, sync scheduling surface, trigger CRM update
    }
}
