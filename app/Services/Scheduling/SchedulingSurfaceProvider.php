<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Contracts\SchedulableEntity;
use App\Models\Inspection\InspectionInstance;
use App\Models\Route\DispatchRoute;
use App\Models\Route\DispatchRouteStop;
use App\Models\Repair\RepairOrder;
use App\Models\Work\ChecklistRun;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlanVisit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * SchedulingSurfaceProvider — canonical scheduling surface aggregator.
 *
 * Aggregates schedulable entities across:
 *   - ServiceJob
 *   - ServicePlanVisit
 *   - InspectionInstance
 *   - ChecklistRun
 *
 * Returns normalized ScheduledEventDTO collections so that the WorkCore
 * job board, FSM dispatch board, and Business Suite calendar can render
 * events through a unified interface.
 */
class SchedulingSurfaceProvider
{
    /**
     * All schedulable entity types supported by this provider.
     *
     * @var list<class-string<SchedulableEntity>>
     */
    private const ENTITY_TYPES = [
        ServiceJob::class,
        ServicePlanVisit::class,
        InspectionInstance::class,
        ChecklistRun::class,
        RepairOrder::class,
    ];

    /**
     * Return all scheduled events that overlap the given date range.
     *
     * @param  Carbon  $from  Range start (inclusive)
     * @param  Carbon  $to    Range end   (inclusive)
     * @return Collection<int, ScheduledEventDTO>
     */
    public function getEventsForRange(Carbon $from, Carbon $to): Collection
    {
        return collect()
            ->merge($this->jobsForRange($from, $to))
            ->merge($this->visitsForRange($from, $to))
            ->merge($this->inspectionsForRange($from, $to))
            ->merge($this->checklistRunsForRange($from, $to))
            ->merge($this->repairsForRange($from, $to))
            ->sortBy('scheduledStart')
            ->values();
    }

    /**
     * Return all scheduled events assigned to a specific user.
     *
     * @return Collection<int, ScheduledEventDTO>
     */
    public function getEventsForUser(int $userId): Collection
    {
        $jobs = ServiceJob::query()
            ->where('assigned_to', $userId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get()
            ->map(fn (ServiceJob $e) => $this->normalise($e));

        $visits = ServicePlanVisit::query()
            ->where('assigned_to', $userId)
            ->whereIn('status', ['pending', 'scheduled'])
            ->get()
            ->map(fn (ServicePlanVisit $e) => $this->normalise($e));

        $inspections = InspectionInstance::query()
            ->where('assigned_to', $userId)
            ->whereNotIn('status', ['completed', 'failed', 'cancelled'])
            ->get()
            ->map(fn (InspectionInstance $e) => $this->normalise($e));

        $checklists = ChecklistRun::query()
            ->where('assigned_to', $userId)
            ->whereNotIn('status', ['completed', 'failed'])
            ->get()
            ->map(fn (ChecklistRun $e) => $this->normalise($e));

        $repairs = RepairOrder::query()
            ->where('assigned_user_id', $userId)
            ->whereNotIn('repair_status', ['completed', 'verified', 'closed', 'cancelled'])
            ->get()
            ->map(fn (RepairOrder $e) => $this->normalise($e));

        return collect()
            ->merge($jobs)
            ->merge($visits)
            ->merge($inspections)
            ->merge($checklists)
            ->merge($repairs)
            ->values();
    }

    /**
     * Return all scheduled events scoped to a premises.
     *
     * @return Collection<int, ScheduledEventDTO>
     */
    public function getEventsForPremises(int $premisesId): Collection
    {
        $jobs = ServiceJob::query()
            ->where('premises_id', $premisesId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get()
            ->map(fn (ServiceJob $e) => $this->normalise($e, $premisesId));

        $visits = ServicePlanVisit::query()
            ->whereHas('plan', fn ($q) => $q->where('premises_id', $premisesId))
            ->whereIn('status', ['pending', 'scheduled'])
            ->get()
            ->map(fn (ServicePlanVisit $e) => $this->normalise($e, $premisesId));

        $inspections = InspectionInstance::query()
            ->where(function ($q) use ($premisesId) {
                $q->where('scope_type', \App\Models\Premises\Premises::class)
                  ->where('scope_id', $premisesId);
            })
            ->whereNotIn('status', ['completed', 'failed', 'cancelled'])
            ->get()
            ->map(fn (InspectionInstance $e) => $this->normalise($e, $premisesId));

        $repairs = RepairOrder::query()
            ->where('premises_id', $premisesId)
            ->whereNotIn('repair_status', ['completed', 'verified', 'closed', 'cancelled'])
            ->get()
            ->map(fn (RepairOrder $e) => $this->normalise($e, $premisesId));

        return collect()
            ->merge($jobs)
            ->merge($visits)
            ->merge($inspections)
            ->merge($repairs)
            ->values();
    }

    /**
     * Return all scheduled events for a customer (across all premises).
     *
     * @return Collection<int, ScheduledEventDTO>
     */
    public function getEventsForCustomer(int $customerId): Collection
    {
        $jobs = ServiceJob::query()
            ->where('customer_id', $customerId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get()
            ->map(fn (ServiceJob $e) => $this->normalise($e, $e->premises_id, $customerId));

        $visits = ServicePlanVisit::query()
            ->whereHas('plan', fn ($q) => $q->where('customer_id', $customerId))
            ->whereIn('status', ['pending', 'scheduled'])
            ->get()
            ->map(fn (ServicePlanVisit $e) => $this->normalise($e, $e->plan?->premises_id, $customerId));

        return collect()
            ->merge($jobs)
            ->merge($visits)
            ->values();
    }

    /**
     * Return all scheduled events assigned to a specific team.
     *
     * Covers ServiceJob (has team_id) and ServicePlanVisit/InspectionInstance
     * where the job linked to the visit belongs to that team.
     *
     * Module 9 (fieldservice_calendar) — team workload calendar surface.
     *
     * @return Collection<int, ScheduledEventDTO>
     */
    public function getEventsForTeam(int $teamId): Collection
    {
        $jobs = ServiceJob::query()
            ->where('team_id', $teamId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get()
            ->map(fn (ServiceJob $e) => $this->normalise($e, $e->premises_id, $e->customer_id));

        $visits = ServicePlanVisit::query()
            ->whereHas('serviceJob', fn ($q) => $q->where('team_id', $teamId))
            ->whereIn('status', ['pending', 'scheduled'])
            ->get()
            ->map(fn (ServicePlanVisit $e) => $this->normalise(
                $e,
                $e->plan?->premises_id,
                $e->plan?->customer_id,
            ));

        $inspections = InspectionInstance::query()
            ->whereHas('serviceJob', fn ($q) => $q->where('team_id', $teamId))
            ->whereNotIn('status', ['completed', 'failed', 'cancelled'])
            ->get()
            ->map(fn (InspectionInstance $e) => $this->normalise($e));

        $repairs = RepairOrder::query()
            ->where('assigned_team_id', $teamId)
            ->whereNotIn('repair_status', ['completed', 'verified', 'closed', 'cancelled'])
            ->get()
            ->map(fn (RepairOrder $e) => $this->normalise(
                $e,
                $e->premises_id,
                $e->customer_id,
            ));

        return collect()
            ->merge($jobs)
            ->merge($visits)
            ->merge($inspections)
            ->merge($repairs)
            ->values();
    }

    /**
     * Public wrapper around normalise() for use by external services and listeners.
     *
     * Module 9 (fieldservice_calendar) — public entity normalisation entry point.
     */
    public function normaliseEntity(
        SchedulableEntity $entity,
        ?int $premisesId = null,
        ?int $customerId = null,
    ): ScheduledEventDTO {
        return $this->normalise($entity, $premisesId, $customerId);
    }

    /**
     * Return all scheduled events for a specific dispatch route on a given date.
     *
     * Module 10 (fieldservice_route) — route-aware scheduling view.
     *
     * @return Collection<int, ScheduledEventDTO>
     */
    public function getEventsForRoute(int $routeId, ?Carbon $date = null): Collection
    {
        $query = DispatchRouteStop::where('route_id', $routeId)
            ->with(['stopItems.schedulable']);

        if ($date !== null) {
            $query->where('route_date', $date->toDateString());
        }

        $events = collect();

        foreach ($query->get() as $stop) {
            foreach ($stop->stopItems as $item) {
                $entity = $item->schedulable;
                if ($entity instanceof SchedulableEntity) {
                    $events->push(
                        $this->normalise($entity, $item->premises_id, $item->customer_id)
                    );
                }
            }
        }

        return $events->sortBy('scheduledStart')->values();
    }

    /**
     * Return all scheduled events for a dispatch route stop (single day-route run).
     *
     * Module 10 (fieldservice_route) — stop-level timeline.
     *
     * @return Collection<int, ScheduledEventDTO>
     */
    public function getEventsForRouteStop(int $routeStopId): Collection
    {
        $stop = DispatchRouteStop::with(['stopItems.schedulable'])->find($routeStopId);

        if ($stop === null) {
            return collect();
        }

        return collect($stop->stopItems)
            ->map(static function ($item) {
                $entity = $item->schedulable;
                if (! ($entity instanceof SchedulableEntity)) {
                    return null;
                }
                return new ScheduledEventDTO(
                    key:            $entity->getSchedulableType() . ':' . $entity->getKey(),
                    entityType:     $entity->getSchedulableType(),
                    entityId:       (int) $entity->getKey(),
                    title:          $entity->getSchedulableTitle(),
                    scheduledStart: $entity->getScheduledStart(),
                    scheduledEnd:   $entity->getScheduledEnd(),
                    assignedUserId: $entity->getAssignedUserId(),
                    status:         $entity->getSchedulableStatus(),
                    priority:       $entity->getSchedulablePriority(),
                    premisesId:     $item->premises_id,
                    customerId:     $item->customer_id,
                );
            })
            ->filter()
            ->values();
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function normalise(
        SchedulableEntity $entity,
        ?int $premisesId = null,
        ?int $customerId = null,
    ): ScheduledEventDTO {
        /** @var \Illuminate\Database\Eloquent\Model&SchedulableEntity $entity */
        return new ScheduledEventDTO(
            key:            $entity->getSchedulableType() . ':' . $entity->getKey(),
            entityType:     $entity->getSchedulableType(),
            entityId:       (int) $entity->getKey(),
            title:          $entity->getSchedulableTitle(),
            scheduledStart: $entity->getScheduledStart(),
            scheduledEnd:   $entity->getScheduledEnd(),
            assignedUserId: $entity->getAssignedUserId(),
            status:         $entity->getSchedulableStatus(),
            priority:       $entity->getSchedulablePriority(),
            premisesId:     $premisesId,
            customerId:     $customerId,
        );
    }

    /**
     * @return Collection<int, ScheduledEventDTO>
     */
    private function jobsForRange(Carbon $from, Carbon $to): Collection
    {
        return ServiceJob::query()
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('scheduled_date_start', [$from, $to])
                  ->orWhereBetween('scheduled_at', [$from, $to]);
            })
            ->get()
            ->map(fn (ServiceJob $e) => $this->normalise($e, $e->premises_id, $e->customer_id));
    }

    /**
     * @return Collection<int, ScheduledEventDTO>
     */
    private function visitsForRange(Carbon $from, Carbon $to): Collection
    {
        return ServicePlanVisit::query()
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('scheduled_date', [$from->toDateString(), $to->toDateString()])
                  ->orWhereBetween('scheduled_for', [$from, $to]);
            })
            ->with('plan')
            ->get()
            ->map(fn (ServicePlanVisit $e) => $this->normalise(
                $e,
                $e->plan?->premises_id,
                $e->plan?->customer_id,
            ));
    }

    /**
     * @return Collection<int, ScheduledEventDTO>
     */
    private function inspectionsForRange(Carbon $from, Carbon $to): Collection
    {
        return InspectionInstance::query()
            ->whereBetween('scheduled_at', [$from, $to])
            ->get()
            ->map(fn (InspectionInstance $e) => $this->normalise($e));
    }

    /**
     * @return Collection<int, ScheduledEventDTO>
     */
    private function checklistRunsForRange(Carbon $from, Carbon $to): Collection
    {
        return ChecklistRun::query()
            ->where(function ($q) use ($from, $to) {
                // Include runs that start within the range OR started before and end after range start
                $q->whereBetween('started_at', [$from, $to])
                  ->orWhere(function ($inner) use ($from, $to) {
                      $inner->where('started_at', '<=', $to)
                            ->where(function ($end) use ($from) {
                                $end->whereNull('completed_at')
                                    ->orWhere('completed_at', '>=', $from);
                            });
                  });
            })
            ->get()
            ->map(fn (ChecklistRun $e) => $this->normalise($e));
    }

    /**
     * @return Collection<int, ScheduledEventDTO>
     */
    private function repairsForRange(Carbon $from, Carbon $to): Collection
    {
        return RepairOrder::query()
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('scheduled_at', [$from, $to])
                  ->orWhere(function ($inner) use ($from, $to) {
                      $inner->where('scheduled_at', '<=', $to)
                            ->where(function ($end) use ($from) {
                                $end->whereNull('completed_at')
                                    ->orWhere('completed_at', '>=', $from);
                            });
                  });
            })
            ->get()
            ->map(fn (RepairOrder $e) => $this->normalise($e, $e->premises_id, $e->customer_id));
    }
}
