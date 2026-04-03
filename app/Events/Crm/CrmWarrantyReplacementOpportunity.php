<?php

declare(strict_types=1);

namespace App\Events\Crm;

use App\Models\Equipment\EquipmentWarranty;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when expired or rejected warranty creates a replacement sales opportunity.
 *
 * Module 8 (fieldservice_equipment_warranty) — crm_warranty_replacement_opportunity signal.
 */
class CrmWarrantyReplacementOpportunity
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly EquipmentWarranty $warranty) {}
}
