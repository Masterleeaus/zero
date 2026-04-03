<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Premises\Premises;
use App\Models\Repair\RepairOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a repair order at a premises is escalated in priority.
 *
 * Corresponds to: premises_repair_escalated signal.
 */
class PremisesRepairEscalated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Premises $premises,
        public readonly RepairOrder $repair,
    ) {}
}
