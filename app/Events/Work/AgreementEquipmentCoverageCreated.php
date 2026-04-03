<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Equipment\InstalledEquipment;
use App\Models\Work\ServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an agreement coverage is created for a specific installed equipment unit.
 *
 * Mirrors Odoo fieldservice_sale_agreement_equipment_stock:
 *   equipment sold through agreement → coverage activation.
 */
class AgreementEquipmentCoverageCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceAgreement $agreement,
        public readonly InstalledEquipment $installedEquipment,
    ) {}
}
