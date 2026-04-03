<?php

declare(strict_types=1);

namespace App\Listeners\Repair;

use App\Events\Repair\RepairDiagnosisRecorded;
use App\Events\Repair\RepairSpecialistRequired;
use App\Events\Repair\RepairQuoteRequired;
use App\Events\Repair\RepairPartsPending;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * React to a repair diagnosis being recorded.
 *
 * Emits secondary signals based on diagnosis flags:
 * specialist required, quote required, parts pending.
 */
class RepairDiagnosisRecordedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(RepairDiagnosisRecorded $event): void
    {
        $repair    = $event->repair;
        $diagnosis = $event->diagnosis;

        try {
            if ($diagnosis->requires_specialist) {
                RepairSpecialistRequired::dispatch($repair, $diagnosis);
            }

            if ($diagnosis->requires_quote) {
                RepairQuoteRequired::dispatch($repair);
            }

            if ($diagnosis->requires_parts) {
                RepairPartsPending::dispatch($repair);
            }
        } catch (\Throwable $th) {
            Log::error('RepairDiagnosisRecordedListener: ' . $th->getMessage(), [
                'repair_id'    => $repair->id,
                'diagnosis_id' => $diagnosis->id,
            ]);
        }
    }
}
