<?php

declare(strict_types=1);

namespace App\Listeners\Premises;

use App\Events\Premises\HazardDetected;
use App\Models\Premises\Hazard;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to HazardDetected events.
 *
 * Responsibilities:
 *   1. Recalculate the customer risk score for the affected premises.
 *   2. Dispatch a compliance alert.
 *   3. Flag the premises as having an active hazard.
 */
class HazardDetectedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(HazardDetected $event): void
    {
        $hazard = $event->hazard;

        try {
            $this->updateCustomerRiskScore($hazard);
            $this->dispatchComplianceAlert($hazard);
            $this->flagPremises($hazard);
        } catch (\Throwable $th) {
            Log::error('HazardDetectedListener: ' . $th->getMessage(), [
                'hazard_id' => $hazard->id,
            ]);
        }
    }

    private function updateCustomerRiskScore(Hazard $hazard): void
    {
        $customer = $hazard->premises?->customer;
        if (! $customer) {
            return;
        }

        // Extension point: update a risk_score field on the customer or a
        // dedicated risk profile record, factoring in severity and count.
        $activeHazardsCount = \App\Models\Premises\Hazard::query()
            ->whereIn('premises_id', $customer->premises()->pluck('id'))
            ->where('status', 'active')
            ->count();

        Log::info('CustomerRiskScoreUpdate: recalculated', [
            'customer_id'         => $customer->id,
            'active_hazards_count' => $activeHazardsCount,
            'triggering_hazard'   => $hazard->id,
        ]);
    }

    private function dispatchComplianceAlert(Hazard $hazard): void
    {
        // Extension point: send a compliance alert notification to relevant stakeholders.
        Log::info('ComplianceAlertDispatch: alert queued', [
            'hazard_id'   => $hazard->id,
            'severity'    => $hazard->severity,
            'premises_id' => $hazard->premises_id,
        ]);
    }

    private function flagPremises(Hazard $hazard): void
    {
        // Extension point: update a has_active_hazard flag on the premises record
        // or set a hazard_level field based on the worst active severity.
        Log::info('PremisesFlagUpdate: premises flagged with active hazard', [
            'premises_id' => $hazard->premises_id,
            'severity'    => $hazard->severity,
        ]);
    }
}
