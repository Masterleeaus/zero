<?php

declare(strict_types=1);

namespace App\Events\Equipment;

use App\Models\Equipment\WarrantyClaim;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a warranty claim is completed.
 *
 * Module 8 (fieldservice_equipment_warranty) — warranty lifecycle signal.
 */
class WarrantyClaimCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly WarrantyClaim $claim) {}
}
