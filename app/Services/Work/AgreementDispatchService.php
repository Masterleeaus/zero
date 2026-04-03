<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Models\Work\ServiceJob;

/**
 * AgreementDispatchService — agreement, warranty, repair, and sale-awareness
 * for the dispatch engine (Stage G).
 *
 * Exposes canonical helpers that the dispatch priority and readiness layers
 * consume to weight, gate, or contextualise job dispatch decisions.
 *
 * Public API:
 *   dispatchCoverageEligible(ServiceJob)        → bool
 *   dispatchWarrantyEligible(ServiceJob)        → bool
 *   dispatchRepairBlocked(ServiceJob)           → bool
 *   dispatchSaleCommitmentPriority(ServiceJob)  → float
 *   dispatchProjectContext(ServiceJob)          → array
 */
class AgreementDispatchService
{
    /**
     * Returns true when the job is covered by an active service agreement.
     *
     * Uses the agreement relationship and active scope already established
     * on ServiceAgreement. Does not duplicate coverage logic from
     * ContractEntitlementService — delegates to agreement status only.
     */
    public function dispatchCoverageEligible(ServiceJob $job): bool
    {
        if (!$job->agreement_id) {
            return false;
        }

        $agreement = $job->agreement;
        if (!$agreement) {
            return false;
        }

        return $agreement->status === 'active';
    }

    /**
     * Returns true when the job qualifies for warranty coverage.
     *
     * Conditions: job is flagged as a warranty job AND has a linked
     * warranty claim that is not already resolved.
     */
    public function dispatchWarrantyEligible(ServiceJob $job): bool
    {
        if (!$job->is_warranty_job || !$job->warranty_claim_id) {
            return false;
        }

        $claim = $job->warrantyClaim;
        if (!$claim) {
            return false;
        }

        // Resolved or rejected claims are not dispatch-eligible for warranty
        $closedStatuses = ['resolved', 'rejected', 'closed'];
        return !in_array($claim->status ?? '', $closedStatuses, true);
    }

    /**
     * Returns true when an open repair order blocks the job from being dispatched.
     *
     * A repair order blocks dispatch when it requires parts or is awaiting
     * authorisation — the technician cannot execute the job until the repair
     * work is complete or cleared.
     */
    public function dispatchRepairBlocked(ServiceJob $job): bool
    {
        if (!method_exists($job, 'repairOrders')) {
            return false;
        }

        return $job->repairOrders()
            ->whereIn('status', ['parts_required', 'awaiting_authorisation', 'on_hold'])
            ->exists();
    }

    /**
     * Returns a priority weight (0.0–1.0) based on sale commitment context.
     *
     * Jobs originating from a quote/sale receive a higher priority weight
     * because they represent explicit customer commitments.
     */
    public function dispatchSaleCommitmentPriority(ServiceJob $job): float
    {
        // Job linked to a sale line (explicit sale commitment)
        if ($job->sale_line_id) {
            return 0.9;
        }

        // Job linked to a quote
        if ($job->quote_id) {
            return 0.7;
        }

        // Job linked to an active agreement
        if ($job->agreement_id && $this->dispatchCoverageEligible($job)) {
            return 0.6;
        }

        // Standard job
        return 0.3;
    }

    /**
     * Returns project context for the job if it is part of a field service project.
     *
     * The dispatch board uses this to group and sequence jobs belonging to the
     * same project execution context.
     */
    public function dispatchProjectContext(ServiceJob $job): array
    {
        if (!$job->project_id) {
            return ['has_project' => false];
        }

        $project = $job->project ?? null;

        return [
            'has_project'      => true,
            'project_id'       => $job->project_id,
            'project_task_ref' => $job->project_task_ref,
            'project_name'     => $project?->name,
            'project_status'   => $project?->status,
        ];
    }
}
