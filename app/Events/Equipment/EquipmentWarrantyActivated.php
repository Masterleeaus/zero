<?php

declare(strict_types=1);

namespace App\Events\Equipment;

use App\Models\Equipment\EquipmentWarranty;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an equipment warranty is activated.
 *
 * Module 8 (fieldservice_equipment_warranty) — warranty lifecycle signal.
 */
class EquipmentWarrantyActivated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly EquipmentWarranty $warranty) {}
}
