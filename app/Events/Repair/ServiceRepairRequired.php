<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairOrder;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a service job identifies a repair is required.
 *
 * Corresponds to: service_repair_required signal.
 */
class ServiceRepairRequired
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $serviceJob,
        public readonly RepairOrder $repair,
    ) {}
}
