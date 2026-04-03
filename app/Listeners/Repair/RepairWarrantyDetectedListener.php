<?php

declare(strict_types=1);

namespace App\Listeners\Repair;

use App\Events\Repair\RepairWarrantyDetected;
use App\Events\Repair\RepairClaimLinked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * React to warranty detection on a repair order.
 *
 * Checks whether an active warranty exists for the associated equipment
 * and emits a claim-linked signal when one is found.
 */
class RepairWarrantyDetectedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(RepairWarrantyDetected $event): void
    {
        $repair = $event->repair;

        try {
            // If a warranty claim is already attached, emit linked signal
            if ($repair->warranty_claim_id) {
                RepairClaimLinked::dispatch($repair);
            }
        } catch (\Throwable $th) {
            Log::error('RepairWarrantyDetectedListener: ' . $th->getMessage(), [
                'repair_id' => $repair->id,
            ]);
        }
    }
}
