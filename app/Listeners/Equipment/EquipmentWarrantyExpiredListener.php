<?php

declare(strict_types=1);

namespace App\Listeners\Equipment;

use App\Events\Equipment\EquipmentWarrantyExpired;
use App\Events\Crm\CrmWarrantyReplacementOpportunity;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to an equipment warranty expiring.
 *
 * Emits a CRM-level crm_warranty_replacement_opportunity signal so the
 * sales pipeline can generate a replacement quote if appropriate.
 *
 * Module 8 (fieldservice_equipment_warranty).
 */
class EquipmentWarrantyExpiredListener
{
    public function handle(EquipmentWarrantyExpired $event): void
    {
        try {
            $event->warranty->syncStatus();
            CrmWarrantyReplacementOpportunity::dispatch($event->warranty);
        } catch (\Throwable $th) {
            Log::error('EquipmentWarrantyExpiredListener: ' . $th->getMessage(), [
                'warranty_id' => $event->warranty->id,
            ]);
        }
    }
}
