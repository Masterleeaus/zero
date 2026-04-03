<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a warranty claim is successfully linked to a repair order.
 *
 * Corresponds to: repair_claim_linked signal.
 */
class RepairClaimLinked
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly RepairOrder $repair) {}
}
