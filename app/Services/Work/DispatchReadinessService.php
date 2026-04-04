<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Models\Route\DispatchRoute;
use App\Models\Work\ServiceJob;

/**
 * DispatchReadinessService — canonical readiness engine for the dispatch graph.
 *
 * All readiness evaluation must flow through this service rather than through
 * isolated Easy Dispatch helpers. Integrates vehicle, stock, agreement, and
 * kanban intelligence already merged into the canonical graph.
 *
 * Public API (Stage C helpers):
 *   dispatchReadiness(ServiceJob)                 → array
 *   dispatchBlockers(ServiceJob)                  → array
 *   dispatchPriorityScore(ServiceJob)             → float
 *   dispatchETAContext(ServiceJob, ?DispatchRoute) → array
 *   dispatchCapacityFit(ServiceJob, DispatchRoute) → bool
 *   dispatchConflictReasons(ServiceJob)            → array
 */
class DispatchReadinessService
{
    public function __construct(
        protected DispatchConstraintService $constraintService,
        protected VehicleDispatchService $vehicleDispatch,
        protected StockDispatchService $stockDispatch,
        protected AgreementDispatchService $agreementDispatch,
    ) {}

    /**
     * Full readiness snapshot for a job.
     *
     * Returns a structured array summarising every readiness dimension so
     * that dispatch board UI, event payloads, and priority engines can all
     * consume a single canonical source.
     */
    public function dispatchReadiness(ServiceJob $job): array
    {
        $blockers = $this->dispatchBlockers($job);
        $ready    = empty($blockers);

        return [
            'job_id'           => $job->id,
            'ready'            => $ready,
            'priority_score'   => $this->dispatchPriorityScore($job),
            'blockers'         => $blockers,
            'sla_urgency'      => $this->constraintService->evaluateSlaUrgency($job),
            'kanban_state'     => $job->kanban_state ?? 'normal',
            'readiness_score'  => $job->readiness_score ?? null,
            'vehicle_ready'    => $this->vehicleDispatch->isJobVehicleReady($job),
            'stock_ready'      => $this->stockDispatch->dispatchStockReady($job),
            'agreement_eligible' => $this->agreementDispatch->dispatchCoverageEligible($job),
            'checked_at'       => now()->toIso8601String(),
        ];
    }

    /**
     * Returns a flat list of human-readable blocker strings for a job.
     *
     * Draws from vehicle, stock, agreement, and kanban blocker sources.
     */
    public function dispatchBlockers(ServiceJob $job): array
    {
        $blockers = [];

        // Kanban blockers already stored on the job
        if (method_exists($job, 'fsmJobBlockers')) {
            $job->fsmJobBlockers->each(function ($b) use (&$blockers) {
                $blockers[] = $b->description ?? $b->blocker_type;
            });
        }

        // Vehicle blockers
        $vehicleBlockers = $this->vehicleDispatch->vehicleBlockers($job);
        foreach ($vehicleBlockers as $reason) {
            $blockers[] = $reason;
        }

        // Stock blockers
        $partsBlockers = $this->stockDispatch->dispatchPartsBlockers($job);
        foreach ($partsBlockers as $reason) {
            $blockers[] = $reason;
        }

        // Agreement / repair blockers
        if ($this->agreementDispatch->dispatchRepairBlocked($job)) {
            $blockers[] = 'Pending repair order must be resolved before dispatch';
        }

        return array_values(array_unique($blockers));
    }

    /**
     * Canonical priority score (0–100).
     *
     * Combines SLA urgency, kanban priority, and agreement commitment weight.
     */
    public function dispatchPriorityScore(ServiceJob $job): float
    {
        $sla = $this->constraintService->evaluateSlaUrgency($job);

        // Kanban priority score if available (0–100 range)
        $kanbanScore = 0.0;
        if (method_exists($job, 'fsmPriorityScore') && $job->fsmPriorityScore) {
            $kanbanScore = (float) ($job->fsmPriorityScore->priority_score ?? 0);
        }

        // Agreement sale commitment weight
        $agreementWeight = $this->agreementDispatch->dispatchSaleCommitmentPriority($job);

        // Weighted blend: SLA 50%, kanban 30%, agreement 20%
        $score = ($sla * 50) + ($kanbanScore * 0.30) + ($agreementWeight * 20);

        return min(100.0, round($score, 2));
    }

    /**
     * ETA context for the job on an optional route.
     *
     * Returns estimated arrival window, travel time, and nearest stop
     * data so the dispatch board can surface real-time ETA indicators.
     */
    public function dispatchETAContext(ServiceJob $job, ?DispatchRoute $route = null): array
    {
        $eta = [
            'job_id'                  => $job->id,
            'scheduled_at'            => $job->scheduled_at?->toIso8601String(),
            'scheduled_date_start'    => $job->scheduled_date_start?->toIso8601String(),
            'scheduled_duration_mins' => $job->scheduled_duration,
            'estimated_arrival_at'    => null,
            'travel_estimate_mins'    => null,
            'route_id'                => $route?->id,
            'route_name'              => $route?->name,
        ];

        // Pull travel estimate from the active dispatch assignment if available
        $assignment = $job->dispatchAssignments()->where('status', 'confirmed')->latest()->first();
        if ($assignment && $assignment->travel_estimate_mins) {
            $eta['travel_estimate_mins'] = $assignment->travel_estimate_mins;

            if ($job->scheduled_at) {
                $eta['estimated_arrival_at'] = $job->scheduled_at
                    ->copy()
                    ->subMinutes((int) $assignment->travel_estimate_mins)
                    ->toIso8601String();
            }
        }

        return $eta;
    }

    /**
     * Returns true when the job can fit into the given route's capacity.
     */
    public function dispatchCapacityFit(ServiceJob $job, DispatchRoute $route): bool
    {
        return $this->vehicleDispatch->vehicleCapacityScore($route->vehicle, $job) > 0.0;
    }

    /**
     * Returns conflict reasons for the given job.
     *
     * Delegates to vehicle, stock, and constraint layers to build a
     * comprehensive conflict list for dispatcher review.
     */
    public function dispatchConflictReasons(ServiceJob $job): array
    {
        $reasons = [];

        // Scheduling overlap check via assigned route stops
        $assignedStop = $job->routeStopItems()->first();
        if ($assignedStop) {
            $overlapItems = $assignedStop->routeStop->items()
                ->where('id', '!=', $assignedStop->id)
                ->where(function ($q) use ($assignedStop) {
                    $q->where('estimated_arrival_at', $assignedStop->estimated_arrival_at)
                      ->orWhere('sequence', $assignedStop->sequence);
                })
                ->get();

            if ($overlapItems->isNotEmpty()) {
                $reasons[] = 'Scheduling overlap detected on route stop';
            }
        }

        // Stock material risk
        if ($this->stockDispatch->dispatchMaterialRisk($job)) {
            $reasons[] = 'Material availability risk — stock check required before dispatch';
        }

        // Vehicle blocked
        $vehicleBlockers = $this->vehicleDispatch->vehicleBlockers($job);
        foreach ($vehicleBlockers as $b) {
            $reasons[] = $b;
        }

        return array_values(array_unique($reasons));
    }
}
