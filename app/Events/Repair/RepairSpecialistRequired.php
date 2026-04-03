<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairDiagnosis;
use App\Models\Repair\RepairOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a diagnosis flags the need for a specialist technician.
 *
 * Corresponds to: repair_specialist_required signal.
 */
class RepairSpecialistRequired
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly RepairOrder $repair,
        public readonly RepairDiagnosis $diagnosis,
    ) {}
}
