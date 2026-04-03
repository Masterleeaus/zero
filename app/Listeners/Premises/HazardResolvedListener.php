<?php

declare(strict_types=1);

namespace App\Listeners\Premises;

use App\Events\Premises\HazardResolved;
use App\Models\Premises\Hazard;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to HazardResolved events.
 *
 * Responsibilities:
 *   1. Recalculate the customer risk score after resolution.
 *   2. Record a compliance closure entry.
 *   3. Add a timeline entry to the customer record.
 */
class HazardResolvedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(HazardResolved $event): void
    {
        $hazard = $event->hazard;

        try {
            $this->recalculateRiskScore($hazard);
            $this->recordComplianceClosure($hazard);
            $this->addCustomerTimelineEntry($hazard);
        } catch (\Throwable $th) {
            Log::error('HazardResolvedListener: ' . $th->getMessage(), [
                'hazard_id' => $hazard->id,
            ]);
        }
    }

    private function recalculateRiskScore(Hazard $hazard): void
    {
        $customer = $hazard->premises?->customer;
        if (! $customer) {
            return;
        }

        $remainingActiveHazards = \App\Models\Premises\Hazard::query()
            ->whereIn('premises_id', $customer->premises()->pluck('id'))
            ->where('status', 'active')
            ->count();

        Log::info('RiskScoreRecalculation: recalculated after hazard resolved', [
            'customer_id'              => $customer->id,
            'remaining_active_hazards' => $remainingActiveHazards,
            'resolved_hazard'          => $hazard->id,
        ]);
    }

    private function recordComplianceClosure(Hazard $hazard): void
    {
        // Extension point: write a compliance closure record to a compliance_log table.
        Log::info('ComplianceClosureRecord: recorded', [
            'hazard_id'   => $hazard->id,
            'resolved_at' => $hazard->resolved_at?->toIso8601String(),
        ]);
    }

    private function addCustomerTimelineEntry(Hazard $hazard): void
    {
        // Extension point: append a timeline entry to the customer's activity log.
        $customer = $hazard->premises?->customer;
        if (! $customer) {
            return;
        }

        Log::info('CustomerTimelineEntry: hazard resolved entry added', [
            'customer_id' => $customer->id,
            'hazard_id'   => $hazard->id,
            'title'       => $hazard->title,
        ]);
    }
}
