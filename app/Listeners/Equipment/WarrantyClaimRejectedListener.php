<?php

declare(strict_types=1);

namespace App\Listeners\Equipment;

use App\Events\Equipment\WarrantyClaimRejected;
use App\Events\Crm\CrmWarrantyClaimRejected;
use App\Events\Crm\CrmWarrantyReplacementOpportunity;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to a warranty claim being rejected.
 *
 * Emits crm_warranty_claim_rejected and crm_warranty_replacement_opportunity
 * signals so the sales pipeline can follow up with a replacement quote.
 *
 * Module 8 (fieldservice_equipment_warranty).
 */
class WarrantyClaimRejectedListener
{
    public function handle(WarrantyClaimRejected $event): void
    {
        try {
            CrmWarrantyClaimRejected::dispatch($event->claim);
            CrmWarrantyReplacementOpportunity::dispatch($event->claim->warranty);
        } catch (\Throwable $th) {
            Log::error('WarrantyClaimRejectedListener: ' . $th->getMessage(), [
                'claim_id' => $event->claim->id,
            ]);
        }
    }
}
