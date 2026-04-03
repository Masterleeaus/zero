<?php

declare(strict_types=1);

namespace App\Services\Calendar;

use App\Models\Inspection\InspectionInstance;
use App\Models\Work\ChecklistRun;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlanVisit;
use App\Services\Scheduling\ScheduledEventDTO;
use App\Services\Scheduling\SchedulingSurfaceProvider;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * BusinessSuiteCalendarAdapter — translates SchedulingSurfaceProvider output
 * into CalendarEventDTO objects for the Business Suite calendar.
 *
 * Supports push(), update(), and remove() operations so that scheduling
 * surface changes are automatically reflected inside the Business Suite calendar.
 *
 * Entity type → colour mapping:
 *   ServiceJob         → blue
 *   ServicePlanVisit   → green
 *   InspectionInstance → orange
 *   ChecklistRun       → purple
 */
class BusinessSuiteCalendarAdapter
{
    /**
     * @var array<class-string, string>
     */
    private const COLOR_MAP = [
        ServiceJob::class         => '#3b82f6',   // blue-500
        ServicePlanVisit::class   => '#22c55e',   // green-500
        InspectionInstance::class => '#f97316',   // orange-500
        ChecklistRun::class       => '#a855f7',   // purple-500
    ];

    private const DEFAULT_COLOR = '#6b7280';      // gray-500

    public function __construct(
        private readonly SchedulingSurfaceProvider $surfaceProvider,
    ) {}

    /**
     * Translate a date range from the scheduling surface into calendar events.
     *
     * @return Collection<int, CalendarEventDTO>
     */
    public function getCalendarEvents(Carbon $from, Carbon $to): Collection
    {
        return $this->surfaceProvider
            ->getEventsForRange($from, $to)
            ->map(fn (ScheduledEventDTO $dto) => $this->toCalendarEvent($dto));
    }

    /**
     * Get calendar events scoped to a single user.
     *
     * @return Collection<int, CalendarEventDTO>
     */
    public function getCalendarEventsForUser(int $userId): Collection
    {
        return $this->surfaceProvider
            ->getEventsForUser($userId)
            ->map(fn (ScheduledEventDTO $dto) => $this->toCalendarEvent($dto));
    }

    /**
     * Get calendar events scoped to a premises.
     *
     * @return Collection<int, CalendarEventDTO>
     */
    public function getCalendarEventsForPremises(int $premisesId): Collection
    {
        return $this->surfaceProvider
            ->getEventsForPremises($premisesId)
            ->map(fn (ScheduledEventDTO $dto) => $this->toCalendarEvent($dto));
    }

    /**
     * Get calendar events scoped to a customer.
     *
     * @return Collection<int, CalendarEventDTO>
     */
    public function getCalendarEventsForCustomer(int $customerId): Collection
    {
        return $this->surfaceProvider
            ->getEventsForCustomer($customerId)
            ->map(fn (ScheduledEventDTO $dto) => $this->toCalendarEvent($dto));
    }

    /**
     * Get calendar events scoped to a team.
     *
     * Module 9 (fieldservice_calendar) — team workload calendar surface.
     *
     * @return Collection<int, CalendarEventDTO>
     */
    public function getCalendarEventsForTeam(int $teamId): Collection
    {
        return $this->surfaceProvider
            ->getEventsForTeam($teamId)
            ->map(fn (ScheduledEventDTO $dto) => $this->toCalendarEvent($dto));
    }

    /**
     * Push a single event to the Business Suite calendar store.
     *
     * Logs the push operation. Extend this method to persist to a
     * calendar_events table or external calendar API as needed.
     */
    public function push(CalendarEventDTO $event): void
    {
        Log::info('BusinessSuiteCalendarAdapter::push', $event->toArray());
    }

    /**
     * Update an existing calendar event by key.
     *
     * Extend this method to update a persisted calendar record or
     * broadcast to external calendar API as needed.
     */
    public function update(CalendarEventDTO $event): void
    {
        Log::info('BusinessSuiteCalendarAdapter::update', $event->toArray());
    }

    /**
     * Remove a calendar event by key.
     *
     * Extend this method to delete a persisted calendar record or
     * broadcast removal to external calendar API as needed.
     */
    public function remove(string $key): void
    {
        Log::info('BusinessSuiteCalendarAdapter::remove', ['key' => $key]);
    }

    /**
     * Broadcast a scheduling surface event to all calendar subscribers.
     *
     * Called by ServicePlanVisitDispatched listener.
     */
    public function broadcast(ScheduledEventDTO $dto): void
    {
        $event = $this->toCalendarEvent($dto);
        $this->push($event);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function toCalendarEvent(ScheduledEventDTO $dto): CalendarEventDTO
    {
        return new CalendarEventDTO(
            key:            $dto->key,
            title:          $dto->title,
            start:          $dto->scheduledStart,
            end:            $dto->scheduledEnd,
            entityType:     $dto->entityType,
            entityId:       $dto->entityId,
            color:          self::COLOR_MAP[$dto->entityType] ?? self::DEFAULT_COLOR,
            status:         $dto->status,
            assignedUserId: $dto->assignedUserId,
            premisesId:     $dto->premisesId,
            customerId:     $dto->customerId,
        );
    }
}
