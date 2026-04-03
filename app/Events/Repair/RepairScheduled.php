<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a repair order is placed on the scheduling surface.
 *
 * Corresponds to: repair_scheduled signal.
 */
class RepairScheduled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly RepairOrder $repair) {}
}
