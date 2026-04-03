<?php

declare(strict_types=1);

namespace App\Events\Crm;

use App\Models\Equipment\WarrantyClaim;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a warranty claim is rejected, signalling a possible replacement opportunity.
 *
 * Module 8 (fieldservice_equipment_warranty) — crm_warranty_claim_rejected signal.
 */
class CrmWarrantyClaimRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly WarrantyClaim $claim) {}
}
