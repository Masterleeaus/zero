<?php

declare(strict_types=1);

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Models\Work\ServiceAgreement;
use App\Services\Work\ContractEntitlementService;
use App\Services\Work\ContractHealthService;
use App\Services\Work\ContractRenewalService;
use App\Services\Work\ContractSLAService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function __construct(
        private readonly ContractEntitlementService $entitlementService,
        private readonly ContractSLAService $slaService,
        private readonly ContractHealthService $healthService,
        private readonly ContractRenewalService $renewalService,
    ) {}

    /**
     * GET /dashboard/work/contracts/{agreement}/entitlements
     *
     * Return all entitlement summaries for an agreement.
     */
    public function entitlements(ServiceAgreement $agreement): JsonResponse
    {
        $entitlements = $agreement->entitlements()
            ->get()
            ->map(fn ($e) => $this->entitlementService->getRemainingEntitlement($agreement, $e->service_type));

        return response()->json([
            'agreement_id' => $agreement->id,
            'entitlements' => $entitlements->values(),
        ]);
    }

    /**
     * GET /dashboard/work/contracts/{agreement}/sla-status
     *
     * Return SLA status for all open jobs under an agreement.
     */
    public function slaStatus(ServiceAgreement $agreement): JsonResponse
    {
        $jobs = $agreement->jobs()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        $statuses = $jobs->map(fn ($job) => $this->slaService->checkSLAStatus($job));

        return response()->json([
            'agreement_id' => $agreement->id,
            'sla_status'   => $statuses->values(),
        ]);
    }

    /**
     * GET /dashboard/work/contracts/{agreement}/health
     *
     * Return the current health score and flags for an agreement.
     */
    public function health(ServiceAgreement $agreement): JsonResponse
    {
        $score = $this->healthService->refreshHealthScore($agreement);

        return response()->json([
            'agreement_id' => $agreement->id,
            'health_score' => $score,
            'health_flags' => $agreement->fresh()->health_flags ?? [],
        ]);
    }

    /**
     * POST /dashboard/work/contracts/{agreement}/renew
     *
     * Manually renew a contract.
     */
    public function renew(Request $request, ServiceAgreement $agreement): JsonResponse
    {
        $validated = $request->validate([
            'new_expiry'     => ['nullable', 'date'],
            'billing_amount' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle'  => ['nullable', 'string', 'in:monthly,quarterly,annually'],
        ]);

        $newAgreement = $this->renewalService->renewContract($agreement, array_filter($validated));

        return response()->json([
            'status'           => 'renewed',
            'new_agreement_id' => $newAgreement->id,
            'contract_number'  => $newAgreement->contract_number,
        ], 201);
    }

    /**
     * GET /dashboard/work/contracts/renewal-queue
     *
     * Return all agreements due for renewal for the authenticated company.
     */
    public function renewalQueue(Request $request): JsonResponse
    {
        $companyId  = $request->user()->company_id;
        $withinDays = (int) $request->query('within_days', 30);

        $queue = $this->renewalService->getDueForRenewal($companyId, $withinDays);

        return response()->json([
            'company_id'   => $companyId,
            'within_days'  => $withinDays,
            'renewal_queue' => $queue->values(),
        ]);
    }
}
