<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairOrder;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a repair order is created originating from a service job.
 *
 * Corresponds to: repair_created_from_service signal.
 */
class RepairCreatedFromService
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $serviceJob,
        public readonly RepairOrder $repair,
    ) {}
}
