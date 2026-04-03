<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when parts are consumed (used) during a repair.
 *
 * Corresponds to: repair_parts_consumed signal.
 */
class RepairPartsConsumed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly RepairOrder $repair) {}
}
