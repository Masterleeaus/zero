<?php

declare(strict_types=1);

namespace App\Listeners\Equipment;

use App\Events\Equipment\EquipmentWarrantyExpiringSoon;
use App\Events\Crm\CrmWarrantyExpiring;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to an equipment warranty approaching expiry.
 *
 * Emits a CRM-level crm_warranty_expiring signal so the customer
 * pipeline can surface the renewal opportunity.
 *
 * Module 8 (fieldservice_equipment_warranty).
 */
class EquipmentWarrantyExpiringSoonListener
{
    public function handle(EquipmentWarrantyExpiringSoon $event): void
    {
        try {
            CrmWarrantyExpiring::dispatch($event->warranty, $event->daysRemaining);
        } catch (\Throwable $th) {
            Log::error('EquipmentWarrantyExpiringSoonListener: ' . $th->getMessage(), [
                'warranty_id' => $event->warranty->id,
            ]);
        }
    }
}
