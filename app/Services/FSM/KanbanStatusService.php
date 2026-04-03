<?php

declare(strict_types=1);

namespace App\Services\FSM;

use App\Events\Work\JobBlockerAdded;
use App\Events\Work\JobBlockerCleared;
use App\Events\Work\JobKanbanStateChanged;
use App\Models\FSM\FsmJobBlocker;
use App\Models\FSM\FsmJobPriorityScore;
use App\Models\FSM\FsmJobStatusMeta;
use App\Models\Work\ServiceJob;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * KanbanStatusService — fieldservice_kanban_info intelligence layer.
 *
 * Responsibilities:
 *   - calculate readiness flags for a service job
 *   - evaluate and persist blocking reasons
 *   - compute and persist dispatch priority scores
 *   - attach workflow hints consumed by EasyDispatch, dispatch board,
 *     calendar overlays, and the Owner Command dashboard
 *
 * Public API:
 *   getJobKanbanState(ServiceJob)     → array
 *   getDispatchPriority(ServiceJob)   → array
 *   getBlockingReasons(ServiceJob)    → Collection
 *   refresh(ServiceJob)               → FsmJobStatusMeta
 *   addBlocker(ServiceJob, …)         → FsmJobBlocker
 *   clearBlocker(FsmJobBlocker, …)    → FsmJobBlocker
 */
class KanbanStatusService
{
    // ── Kanban state constants (mirrors Odoo FSM kanban_state) ───────────────

    public const STATE_NORMAL              = 'normal';
    public const STATE_BLOCKED             = 'blocked';
    public const STATE_READY_FOR_NEXT      = 'ready_for_next_stage';

    // Priority score weight configuration (must sum to 100)
    private const WEIGHT_URGENCY    = 35;
    private const WEIGHT_SLA        = 25;
    private const WEIGHT_CLIENT     = 20;
    private const WEIGHT_AGREEMENT  = 10;
    private const WEIGHT_EQUIPMENT  = 10;

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Return the full kanban intelligence payload for a job.
     *
     * This is the primary method consumed by:
     *   - Dispatch board
     *   - Command app
     *   - EasyDispatch engine
     *   - Calendar overlays
     *
     * @return array<string, mixed>
     */
    public function getJobKanbanState(ServiceJob $job): array
    {
        $meta     = $this->resolveOrRefreshMeta($job);
        $priority = $this->resolveOrRefreshScore($job);
        $blockers = $this->getActiveBlockers($job);

        return [
            // Core kanban state
            'kanban_state'              => $job->kanban_state ?? self::STATE_NORMAL,
            'kanban_state_label'        => $job->kanban_state_label,
            'readiness_score'           => $job->readiness_score ?? 0,

            // Readiness flags
            'is_ready_to_start'         => $meta->is_ready_to_start,
            'is_waiting_parts'          => $meta->is_waiting_parts,
            'is_blocked'                => $meta->is_blocked,
            'is_overdue'                => $meta->is_overdue,
            'requires_followup'         => $meta->requires_followup,
            'customer_action_pending'   => $meta->customer_action_pending,

            // SLA
            'sla_deadline'              => $job->sla_deadline?->toIso8601String(),
            'sla_breached'              => $job->sla_breached,

            // Dispatch enrichment
            'priority_score'            => $priority->total_score,
            'score_breakdown'           => $priority->score_breakdown ?? [],
            'delay_risk'                => $meta->delay_risk,
            'travel_conflict_flag'      => $meta->travel_conflict_flag,
            'crew_skill_mismatch'       => $meta->crew_skill_mismatch,
            'equipment_missing'         => $meta->equipment_missing,
            'contract_violation'        => $meta->contract_violation,

            // CRM / agreement awareness
            'equipment_warranty_expired'=> $meta->equipment_warranty_expired,
            'agreement_expired'         => $meta->agreement_expired,
            'vip_client_flag'           => $meta->vip_client_flag,

            // Technician
            'technician_prep_done'      => $meta->technician_prep_done,

            // Blocking reasons
            'blockers'                  => $blockers->map(fn (FsmJobBlocker $b) => [
                'id'           => $b->id,
                'type'         => $b->blocker_type,
                'label'        => $b->blocker_label,
                'details'      => $b->details,
            ])->values()->all(),
        ];
    }

    /**
     * Return just the dispatch priority payload for EasyDispatch / RouteOptimizer.
     *
     * @return array<string, mixed>
     */
    public function getDispatchPriority(ServiceJob $job): array
    {
        $score = $this->resolveOrRefreshScore($job);
        $meta  = $this->resolveOrRefreshMeta($job);

        return [
            'job_id'             => $job->id,
            'total_score'        => $score->total_score,
            'urgency_score'      => $score->urgency_score,
            'sla_score'          => $score->sla_score,
            'client_tier_score'  => $score->client_tier_score,
            'agreement_score'    => $score->agreement_score,
            'equipment_score'    => $score->equipment_score,
            'score_breakdown'    => $score->score_breakdown ?? [],
            'delay_risk'         => $meta->delay_risk,
            'is_blocked'         => $meta->is_blocked,
            'sla_breached'       => $job->sla_breached,
        ];
    }

    /**
     * Return all active (unresolved) blocking reasons for a job.
     *
     * @return Collection<int, FsmJobBlocker>
     */
    public function getBlockingReasons(ServiceJob $job): Collection
    {
        return $this->getActiveBlockers($job);
    }

    /**
     * Fully refresh the status meta and priority score for a job.
     *
     * Called after any event that could change job readiness:
     *   - stage change, technician assignment, parts update, SLA update, etc.
     */
    public function refresh(ServiceJob $job): FsmJobStatusMeta
    {
        return DB::transaction(function () use ($job): FsmJobStatusMeta {
            $meta  = $this->computeAndPersistMeta($job);
            $score = $this->computeAndPersistScore($job);

            // Sync kanban_state column on service_jobs
            $newState = $this->deriveKanbanState($meta);
            $oldState = $job->kanban_state ?? self::STATE_NORMAL;

            if ($newState !== $oldState) {
                $job->kanban_state      = $newState;
                $job->kanban_state_label = $this->kanbanStateLabel($newState);
                $job->readiness_score   = $score->total_score;
                $job->save();

                JobKanbanStateChanged::dispatch($job, $oldState, $newState);
            } else {
                $job->readiness_score = $score->total_score;
                $job->save();
            }

            return $meta;
        });
    }

    /**
     * Add a blocking reason to a job, updating kanban state to 'blocked'.
     */
    public function addBlocker(
        ServiceJob $job,
        string $type,
        string $label,
        ?string $details = null,
    ): FsmJobBlocker {
        $blocker = FsmJobBlocker::create([
            'company_id'     => $job->company_id,
            'service_job_id' => $job->id,
            'blocker_type'   => $type,
            'blocker_label'  => $label,
            'details'        => $details,
            'is_resolved'    => false,
        ]);

        JobBlockerAdded::dispatch($job, $blocker);

        // Refresh state so kanban_state is promoted to 'blocked'
        $this->refresh($job);

        return $blocker;
    }

    /**
     * Mark a blocker as resolved and refresh job kanban state.
     */
    public function clearBlocker(
        FsmJobBlocker $blocker,
        ?int $resolvedBy = null,
    ): FsmJobBlocker {
        $blocker->update([
            'is_resolved' => true,
            'resolved_at' => Carbon::now(),
            'resolved_by' => $resolvedBy,
        ]);

        $job = $blocker->serviceJob;

        JobBlockerCleared::dispatch($job, $blocker);

        $this->refresh($job);

        return $blocker;
    }

    // ── Internal compute methods ──────────────────────────────────────────────

    /**
     * Compute and persist the readiness/enrichment meta for a job.
     */
    private function computeAndPersistMeta(ServiceJob $job): FsmJobStatusMeta
    {
        $job->loadMissing([
            'assignedUser',
            'stage',
            'activities',
            'agreement',
            'coveredEquipment',
            'checklists',
        ]);

        $activeBlockers            = $this->getActiveBlockers($job);
        $isBlocked                 = $activeBlockers->isNotEmpty();
        $isOverdue                 = $this->computeIsOverdue($job);
        $isWaitingParts            = $activeBlockers->where('blocker_type', FsmJobBlocker::TYPE_PARTS_MISSING)->isNotEmpty();
        $requiresFollowup          = $this->computeRequiresFollowup($job);
        $customerActionPending     = $this->computeCustomerActionPending($job);
        $isReadyToStart            = $this->computeIsReadyToStart($job, $isBlocked);
        $equipmentWarrantyExpired  = $this->computeEquipmentWarrantyExpired($job);
        $agreementExpired          = $this->computeAgreementExpired($job);
        $vipClientFlag             = $this->computeVipClientFlag($job);
        $equipmentMissing          = $activeBlockers->where('blocker_type', FsmJobBlocker::TYPE_PARTS_MISSING)->isNotEmpty()
                                     || $activeBlockers->where('blocker_type', FsmJobBlocker::TYPE_EQUIPMENT_FAULT)->isNotEmpty();
        $contractViolation         = $activeBlockers->where('blocker_type', FsmJobBlocker::TYPE_CONTRACT_VIOLATION)->isNotEmpty()
                                     || $agreementExpired;

        $data = [
            'company_id'                => $job->company_id,
            'service_job_id'            => $job->id,
            'is_ready_to_start'         => $isReadyToStart,
            'is_waiting_parts'          => $isWaitingParts,
            'is_blocked'                => $isBlocked,
            'is_overdue'                => $isOverdue,
            'requires_followup'         => $requiresFollowup,
            'customer_action_pending'   => $customerActionPending,
            'priority_score'            => 0, // placeholder — overwritten by score pass
            'delay_risk'                => $isOverdue || ($job->sla_deadline && $job->sla_deadline->copy()->subHours(2)->isPast()),
            'travel_conflict_flag'      => false, // extended by RouteService if needed
            'crew_skill_mismatch'       => $activeBlockers->where('blocker_type', FsmJobBlocker::TYPE_SKILL_MISMATCH)->isNotEmpty(),
            'equipment_missing'         => $equipmentMissing,
            'contract_violation'        => $contractViolation,
            'equipment_warranty_expired'=> $equipmentWarrantyExpired,
            'agreement_expired'         => $agreementExpired,
            'vip_client_flag'           => $vipClientFlag,
            'technician_prep_done'      => ! is_null($job->assigned_user_id),
            'refreshed_at'              => Carbon::now(),
        ];

        return FsmJobStatusMeta::updateOrCreate(
            ['service_job_id' => $job->id],
            $data
        );
    }

    /**
     * Compute and persist the priority score for a job.
     */
    private function computeAndPersistScore(ServiceJob $job): FsmJobPriorityScore
    {
        $urgency   = $this->scoreUrgency($job);
        $sla       = $this->scoreSla($job);
        $client    = $this->scoreClientTier($job);
        $agreement = $this->scoreAgreement($job);
        $equipment = $this->scoreEquipment($job);

        $total = (int) round(
            ($urgency   * self::WEIGHT_URGENCY   / 100)
            + ($sla     * self::WEIGHT_SLA       / 100)
            + ($client  * self::WEIGHT_CLIENT    / 100)
            + ($agreement * self::WEIGHT_AGREEMENT / 100)
            + ($equipment * self::WEIGHT_EQUIPMENT / 100)
        );

        $breakdown = [
            'urgency'   => $urgency,
            'sla'       => $sla,
            'client'    => $client,
            'agreement' => $agreement,
            'equipment' => $equipment,
            'weights'   => [
                'urgency'   => self::WEIGHT_URGENCY,
                'sla'       => self::WEIGHT_SLA,
                'client'    => self::WEIGHT_CLIENT,
                'agreement' => self::WEIGHT_AGREEMENT,
                'equipment' => self::WEIGHT_EQUIPMENT,
            ],
        ];

        return FsmJobPriorityScore::updateOrCreate(
            ['service_job_id' => $job->id],
            [
                'company_id'       => $job->company_id,
                'urgency_score'    => $urgency,
                'sla_score'        => $sla,
                'client_tier_score'=> $client,
                'agreement_score'  => $agreement,
                'equipment_score'  => $equipment,
                'total_score'      => $total,
                'score_breakdown'  => $breakdown,
                'scored_at'        => Carbon::now(),
            ]
        );
    }

    // ── Readiness flag evaluators ─────────────────────────────────────────────

    private function computeIsOverdue(ServiceJob $job): bool
    {
        if (in_array($job->status, ['completed', 'cancelled'], true)) {
            return false;
        }

        if ($job->sla_deadline && $job->sla_deadline->isPast()) {
            return true;
        }

        return $job->scheduled_date_end !== null && $job->scheduled_date_end->isPast();
    }

    private function computeIsReadyToStart(ServiceJob $job, bool $isBlocked): bool
    {
        if ($isBlocked || in_array($job->status, ['completed', 'cancelled'], true)) {
            return false;
        }

        // Must have an assigned technician
        if (is_null($job->assigned_user_id)) {
            return false;
        }

        // Must not have unfinished required activities
        if ($job->activities()->where('required', true)->where('state', 'todo')->exists()) {
            return false;
        }

        return true;
    }

    private function computeRequiresFollowup(ServiceJob $job): bool
    {
        if ($job->service_outcome === null) {
            return false;
        }

        return in_array($job->service_outcome, [
            ServiceJob::OUTCOME_COMPLETED_WITH_FOLLOWUP,
            ServiceJob::OUTCOME_RETURN_VISIT_REQUIRED,
            ServiceJob::OUTCOME_RESCHEDULE_REQUIRED,
            ServiceJob::OUTCOME_QUOTE_REQUIRED,
            ServiceJob::OUTCOME_AGREEMENT_REQUIRED,
        ], true);
    }

    private function computeCustomerActionPending(ServiceJob $job): bool
    {
        // Waiting for customer signature
        if ($job->require_signature && is_null($job->signed_on)) {
            return true;
        }

        // Waiting for quote acceptance
        if ($job->quote_id && $job->service_outcome === ServiceJob::OUTCOME_QUOTE_REQUIRED) {
            return true;
        }

        return false;
    }

    private function computeEquipmentWarrantyExpired(ServiceJob $job): bool
    {
        if (! $job->is_warranty_job) {
            return false;
        }

        $claim = $job->warrantyClaim;
        if (! $claim) {
            return false;
        }

        // If the claim is rejected, treat warranty as expired/invalid
        return $claim->status === 'rejected';
    }

    private function computeAgreementExpired(ServiceJob $job): bool
    {
        $agreement = $job->agreement;
        if (! $agreement) {
            return false;
        }

        if (isset($agreement->expired_at) && $agreement->expired_at !== null) {
            return $agreement->expired_at->isPast();
        }

        return $agreement->status === 'expired';
    }

    private function computeVipClientFlag(ServiceJob $job): bool
    {
        $customer = $job->customer;
        if (! $customer) {
            return false;
        }

        // Honour a vip / tier column if present on the customer model
        if (isset($customer->is_vip)) {
            return (bool) $customer->is_vip;
        }

        if (isset($customer->tier)) {
            return in_array($customer->tier, ['vip', 'premium', 'gold'], true);
        }

        return false;
    }

    // ── Score evaluators (each returns 0–100) ─────────────────────────────────

    private function scoreUrgency(ServiceJob $job): int
    {
        return match ($job->priority) {
            'urgent' => 100,
            'high'   => 75,
            'normal' => 40,
            'low'    => 10,
            default  => 40,
        };
    }

    private function scoreSla(ServiceJob $job): int
    {
        if ($job->sla_breached) {
            return 100;
        }

        if (! $job->sla_deadline) {
            return 0;
        }

        // diffInHours with absolute=false returns negative when deadline is past
        $hoursRemaining = Carbon::now()->diffInHours($job->sla_deadline, false);

        if ($hoursRemaining <= 0) {
            // Deadline has passed (or is now) — treat as breached
            return 100;
        }

        if ($hoursRemaining <= 2) {
            return 90;
        }

        if ($hoursRemaining <= 8) {
            return 70;
        }

        if ($hoursRemaining <= 24) {
            return 50;
        }

        return 20;
    }

    private function scoreClientTier(ServiceJob $job): int
    {
        if ($this->computeVipClientFlag($job)) {
            return 100;
        }

        $customer = $job->customer;
        if (! $customer) {
            return 0;
        }

        if (isset($customer->tier)) {
            return match ($customer->tier) {
                'premium', 'gold' => 80,
                'standard'        => 40,
                default           => 20,
            };
        }

        return 20;
    }

    private function scoreAgreement(ServiceJob $job): int
    {
        $agreement = $job->agreement;
        if (! $agreement) {
            return 0;
        }

        // Active agreement = higher priority
        return $agreement->status === 'active' ? 80 : 20;
    }

    private function scoreEquipment(ServiceJob $job): int
    {
        if ($job->is_warranty_job) {
            return $this->computeEquipmentWarrantyExpired($job) ? 60 : 40;
        }

        return 0;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getActiveBlockers(ServiceJob $job): Collection
    {
        return FsmJobBlocker::where('service_job_id', $job->id)
            ->active()
            ->get();
    }

    private function deriveKanbanState(FsmJobStatusMeta $meta): string
    {
        if ($meta->is_blocked) {
            return self::STATE_BLOCKED;
        }

        if ($meta->is_ready_to_start) {
            return self::STATE_READY_FOR_NEXT;
        }

        return self::STATE_NORMAL;
    }

    private function kanbanStateLabel(string $state): string
    {
        return match ($state) {
            self::STATE_BLOCKED        => 'Blocked',
            self::STATE_READY_FOR_NEXT => 'Ready',
            default                    => 'In Progress',
        };
    }

    private function resolveOrRefreshMeta(ServiceJob $job): FsmJobStatusMeta
    {
        $meta = FsmJobStatusMeta::where('service_job_id', $job->id)->first();

        if (! $meta) {
            $meta = $this->computeAndPersistMeta($job);
        }

        return $meta;
    }

    private function resolveOrRefreshScore(ServiceJob $job): FsmJobPriorityScore
    {
        $score = FsmJobPriorityScore::where('service_job_id', $job->id)->first();

        if (! $score) {
            $score = $this->computeAndPersistScore($job);
        }

        return $score;
    }
}
