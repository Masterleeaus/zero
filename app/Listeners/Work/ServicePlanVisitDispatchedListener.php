<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\ServicePlanVisitDispatched;
use App\Services\Calendar\BusinessSuiteCalendarAdapter;
use App\Services\Dispatch\DispatchBoardEventAdapter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to ServicePlanVisitDispatched events.
 *
 * Responsibilities:
 *   1. Enqueue the route in the RouteOptimizer.
 *   2. Confirm technician assignment.
 *   3. Broadcast the new calendar event to Business Suite.
 */
class ServicePlanVisitDispatchedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(
        private readonly DispatchBoardEventAdapter $dispatchAdapter,
        private readonly BusinessSuiteCalendarAdapter $calendarAdapter,
    ) {}

    public function handle(ServicePlanVisitDispatched $event): void
    {
        $visit = $event->visit;
        $job   = $event->job;

        try {
            $this->enqueueRouteOptimizer($visit, $job);
            $this->confirmTechnicianAssignment($job);
            $this->broadcastCalendarEvent($visit, $job);
        } catch (\Throwable $th) {
            Log::error('ServicePlanVisitDispatchedListener: ' . $th->getMessage(), [
                'visit_id' => $visit->id,
                'job_id'   => $job->id,
            ]);
        }
    }

    private function enqueueRouteOptimizer(
        \App\Models\Work\ServicePlanVisit $visit,
        \App\Models\Work\ServiceJob $job,
    ): void {
        // Extension point: push job to RouteOptimizer queue.
        Log::info('RouteOptimizer: enqueue requested', [
            'job_id'     => $job->id,
            'premises_id' => $job->premises_id,
        ]);
    }

    private function confirmTechnicianAssignment(\App\Models\Work\ServiceJob $job): void
    {
        // Extension point: notify the assigned technician via push/mail.
        Log::info('TechnicianAssignmentConfirmed', [
            'job_id'      => $job->id,
            'assigned_to' => $job->assigned_to,
        ]);
    }

    private function broadcastCalendarEvent(
        \App\Models\Work\ServicePlanVisit $visit,
        \App\Models\Work\ServiceJob $job,
    ): void {
        $card = $this->dispatchAdapter->fromEntity(
            $visit,
            $job->premises_id,
            $job->customer_id,
        );

        $calendarEvent = new \App\Services\Calendar\CalendarEventDTO(
            key:            $card->key,
            title:          $card->title,
            start:          $card->scheduledStart,
            end:            $card->scheduledEnd,
            entityType:     $card->entityType,
            entityId:       $card->entityId,
            color:          '#22c55e',
            status:         $card->status,
            assignedUserId: $card->assignedUserId,
            premisesId:     $card->premisesId,
            customerId:     $card->customerId,
        );

        $this->calendarAdapter->push($calendarEvent);

        Log::info('CalendarSync: broadcast dispatched', ['key' => $card->key]);
    }
}
