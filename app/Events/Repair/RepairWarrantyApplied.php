<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when warranty coverage is applied to a repair order, reducing customer payable.
 *
 * Corresponds to: repair_warranty_applied signal.
 */
class RepairWarrantyApplied
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly RepairOrder $repair) {}
}
