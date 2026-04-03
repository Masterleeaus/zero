<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Contracts\SchedulableEntity;
use App\Models\Inspection\InspectionInstance;
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

        return collect()
            ->merge($jobs)
            ->merge($visits)
            ->merge($inspections)
            ->merge($checklists)
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

        return collect()
            ->merge($jobs)
            ->merge($visits)
            ->merge($inspections)
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
            ->whereBetween('started_at', [$from, $to])
            ->get()
            ->map(fn (ChecklistRun $e) => $this->normalise($e));
    }
}
