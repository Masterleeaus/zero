<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\ServicePlanVisitScheduled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to ServicePlanVisitScheduled events.
 *
 * Responsibilities:
 *   1. Trigger dispatch suggestion engine for the newly scheduled visit.
 *   2. Check technician availability for the visit window.
 *   3. Queue a customer notification about the upcoming visit.
 */
class ServicePlanVisitScheduledListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(ServicePlanVisitScheduled $event): void
    {
        $visit = $event->visit;

        try {
            $this->triggerDispatchSuggestion($visit);
            $this->checkTechnicianAvailability($visit);
            $this->notifyCustomerUpcomingVisit($visit);
        } catch (\Throwable $th) {
            Log::error('ServicePlanVisitScheduledListener: ' . $th->getMessage(), [
                'visit_id' => $visit->id,
            ]);
        }
    }

    private function triggerDispatchSuggestion(\App\Models\Work\ServicePlanVisit $visit): void
    {
        // Extension point: inject DispatchSuggestionEngine and call enqueue().
        // The engine should evaluate available technicians for the visit window
        // and produce ranked assignment suggestions.
        Log::info('DispatchSuggestionEngine: suggestion requested', [
            'visit_id'      => $visit->id,
            'scheduled_for' => $visit->scheduled_for?->toIso8601String(),
            'scheduled_date' => $visit->scheduled_date?->toDateString(),
        ]);
    }

    private function checkTechnicianAvailability(\App\Models\Work\ServicePlanVisit $visit): void
    {
        // Extension point: query TechnicianAvailability for the scheduled window.
        Log::info('TechnicianAvailabilityCheck: availability check triggered', [
            'visit_id'      => $visit->id,
            'assigned_to'   => $visit->assigned_to,
        ]);
    }

    private function notifyCustomerUpcomingVisit(\App\Models\Work\ServicePlanVisit $visit): void
    {
        // Extension point: fire a CustomerUpcomingVisitNotification mail/notification.
        $customer = $visit->plan?->customer;
        if (! $customer) {
            return;
        }

        Log::info('CustomerUpcomingVisitNotification: queued', [
            'visit_id'    => $visit->id,
            'customer_id' => $customer->id,
        ]);
    }
}
