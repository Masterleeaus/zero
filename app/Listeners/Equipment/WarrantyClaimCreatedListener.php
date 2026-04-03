<?php

declare(strict_types=1);

namespace App\Listeners\Equipment;

use App\Events\Equipment\WarrantyClaimCreated;
use App\Events\Crm\CrmWarrantyClaimOpened;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to a warranty claim being created.
 *
 * Emits a CRM-level crm_warranty_claim_opened signal so the
 * customer pipeline is notified of the open claim.
 *
 * Module 8 (fieldservice_equipment_warranty).
 */
class WarrantyClaimCreatedListener
{
    public function handle(WarrantyClaimCreated $event): void
    {
        try {
            CrmWarrantyClaimOpened::dispatch($event->claim);
        } catch (\Throwable $th) {
            Log::error('WarrantyClaimCreatedListener: ' . $th->getMessage(), [
                'claim_id' => $event->claim->id,
            ]);
        }
    }
}
