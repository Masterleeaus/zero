<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairDiagnosis;
use App\Models\Repair\RepairOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a diagnosis is recorded against a repair order.
 *
 * Corresponds to: repair_diagnosis_recorded signal.
 */
class RepairDiagnosisRecorded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly RepairOrder $repair,
        public readonly RepairDiagnosis $diagnosis,
    ) {}
}
