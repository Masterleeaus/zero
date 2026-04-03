<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Equipment\InstalledEquipment;
use App\Models\Work\ServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an existing agreement equipment coverage is extended or renewed.
 *
 * Mirrors Odoo fieldservice_sale_agreement_equipment_stock:
 *   subsequent sale on same agreement extends existing coverage period.
 */
class AgreementEquipmentCoverageExtended
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceAgreement $agreement,
        public readonly InstalledEquipment $installedEquipment,
    ) {}
}
