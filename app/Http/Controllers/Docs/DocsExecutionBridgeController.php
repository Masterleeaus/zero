<?php

declare(strict_types=1);

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Core\CoreController;
use App\Models\Inspection\InspectionInstance;
use App\Models\Premises\DocumentInjectionRule;
use App\Models\Work\ServiceJob;
use App\Services\Docs\DocsExecutionBridgeService;
use App\Services\Docs\DocumentSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocsExecutionBridgeController extends CoreController
{
    public function __construct(
        private readonly DocsExecutionBridgeService $bridge,
        private readonly DocumentSearchService      $search,
    ) {}

    /**
     * Get injected documents for a service job.
     * Triggers rule-based injection if none exist yet.
     *
     * GET /dashboard/docs/jobs/{job}
     */
    public function forJob(Request $request, ServiceJob $job): JsonResponse
    {
        $this->authorizeForCompany($request, $job);

        // Run injection if no records exist yet
        if ($job->injectedDocuments()->doesntExist()) {
            $this->bridge->injectForJob($job);
        }

        $documents = $job->injectedDocuments()->with('document')->get();

        return response()->json(['data' => $documents]);
    }

    /**
     * Get injected documents for an inspection instance.
     * Triggers rule-based injection if none exist yet.
     *
     * GET /dashboard/docs/inspections/{inspection}
     */
    public function forInspection(Request $request, InspectionInstance $inspection): JsonResponse
    {
        $this->authorizeForCompany($request, $inspection);

        if ($inspection->injectedDocuments()->doesntExist()) {
            $this->bridge->injectForInspection($inspection);
        }

        $documents = $inspection->injectedDocuments()->with('document')->get();

        return response()->json(['data' => $documents]);
    }

    /**
     * Acknowledge a document within a job or inspection context.
     *
     * POST /dashboard/docs/acknowledge
     */
    public function acknowledge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_id'  => ['required', 'integer', 'exists:facility_documents,id'],
            'context_id'   => ['required', 'integer'],
            'context_type' => ['required', 'string', 'in:job,inspection'],
        ]);

        $this->bridge->acknowledgeDocument(
            documentId:  $validated['document_id'],
            contextId:   $validated['context_id'],
            contextType: $validated['context_type'],
            user:        $request->user(),
        );

        return response()->json(['status' => 'acknowledged']);
    }

    /**
     * Semantic document search.
     *
     * GET /dashboard/docs/search
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'                 => ['required', 'string', 'min:2'],
            'document_category' => ['nullable', 'string'],
            'is_mandatory'      => ['nullable', 'boolean'],
        ]);

        $companyId = $request->user()?->company_id;

        if (! $companyId) {
            return response()->json(['error' => 'Company not resolved.'], 422);
        }

        $filters = array_filter([
            'document_category' => $validated['document_category'] ?? null,
            'is_mandatory'      => isset($validated['is_mandatory']) ? (bool) $validated['is_mandatory'] : null,
        ], static fn ($v) => $v !== null);

        $results = $this->search->semanticSearch($validated['q'], $companyId, $filters);

        return response()->json(['data' => $results]);
    }

    /**
     * List document injection rules for the authenticated company.
     *
     * GET /dashboard/docs/rules
     */
    public function rules(Request $request): JsonResponse
    {
        $companyId = $request->user()?->company_id;

        if (! $companyId) {
            return response()->json(['error' => 'Company not resolved.'], 422);
        }

        $rules = DocumentInjectionRule::query()
            ->where('company_id', $companyId)
            ->with('document')
            ->orderBy('rule_type')
            ->get();

        return response()->json(['data' => $rules]);
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    private function authorizeForCompany(Request $request, mixed $model): void
    {
        $companyId = $request->user()?->company_id;

        if (! $companyId || $model->company_id !== $companyId) {
            abort(403);
        }
    }
}
