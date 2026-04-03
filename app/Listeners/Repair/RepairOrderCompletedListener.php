<?php

declare(strict_types=1);

namespace App\Listeners\Repair;

use App\Events\Repair\RepairOrderCompleted;
use App\Events\Repair\PremisesRepairClosed;
use App\Events\Repair\RepairInvoiceGenerated;
use App\Events\Repair\CrmReplacementCandidate;
use App\Events\Repair\CrmAgreementCandidate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * React to repair order completion.
 *
 * Emits secondary signals: premises closure, invoice generation trigger,
 * and CRM upsell/upgrade candidate signals.
 */
class RepairOrderCompletedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(RepairOrderCompleted $event): void
    {
        $repair = $event->repair;

        try {
            // Notify premises that the repair is closed
            if ($repair->premises_id && $repair->premises) {
                PremisesRepairClosed::dispatch($repair->premises, $repair);
            }

            // Trigger invoice if not warranty covered
            if (! $repair->isWarrantyCovered()) {
                RepairInvoiceGenerated::dispatch($repair);
            }

            // CRM: emit replacement candidate when repair is emergency or high-severity
            if (in_array($repair->repair_type, [
                \App\Models\Repair\RepairOrder::TYPE_EMERGENCY,
                \App\Models\Repair\RepairOrder::TYPE_BREAKDOWN,
            ], true)) {
                CrmReplacementCandidate::dispatch($repair);
            }

            // CRM: emit agreement candidate if no agreement is attached
            if (! $repair->agreement_id) {
                CrmAgreementCandidate::dispatch($repair);
            }
        } catch (\Throwable $th) {
            Log::error('RepairOrderCompletedListener: ' . $th->getMessage(), [
                'repair_id' => $repair->id,
            ]);
        }
    }
}
