<?php

declare(strict_types=1);

namespace App\Events\Crm;

use App\Models\Equipment\EquipmentWarranty;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a CRM-visible warranty is approaching expiry.
 *
 * Module 8 (fieldservice_equipment_warranty) — crm_warranty_expiring signal.
 * Exposed to Customer, Deal, and Enquiry CRM contexts.
 */
class CrmWarrantyExpiring
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly EquipmentWarranty $warranty,
        public readonly int $daysRemaining,
    ) {}
}
