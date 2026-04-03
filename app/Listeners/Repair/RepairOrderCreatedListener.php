<?php

declare(strict_types=1);

namespace App\Listeners\Repair;

use App\Events\Repair\RepairOrderCreated;
use App\Events\Repair\PremisesRepairCreated;
use App\Events\Repair\RepairWarrantyDetected;
use App\Events\Repair\RepairClaimRequired;
use App\Events\Repair\CrmRepairDetected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * React to repair order creation.
 *
 * Emits secondary signals: premises notification, warranty detection, CRM signal.
 */
class RepairOrderCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(RepairOrderCreated $event): void
    {
        $repair = $event->repair;

        try {
            // Emit premises signal if premises is attached
            if ($repair->premises_id && $repair->premises) {
                PremisesRepairCreated::dispatch($repair->premises, $repair);
            }

            // Emit warranty detection signal if equipment has warranty coverage
            if ($repair->equipment_id || $repair->installed_equipment_id) {
                RepairWarrantyDetected::dispatch($repair);
            }

            // Emit claim required signal for warranty-type repairs
            if ($repair->repair_type === \App\Models\Repair\RepairOrder::TYPE_WARRANTY) {
                RepairClaimRequired::dispatch($repair);
            }

            // Emit CRM signal for all new repairs
            CrmRepairDetected::dispatch($repair);
        } catch (\Throwable $th) {
            Log::error('RepairOrderCreatedListener: ' . $th->getMessage(), [
                'repair_id' => $repair->id,
            ]);
        }
    }
}
