<?php

declare(strict_types=1);

namespace App\Http\Controllers\Trust;

use App\Http\Controllers\Controller;
use App\Models\Trust\TrustLedgerEntry;
use App\Services\Trust\TrustLedgerService;
use App\Services\Trust\TrustVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrustLedgerController extends Controller
{
    public function __construct(
        protected TrustLedgerService $ledgerService,
        protected TrustVerificationService $verificationService,
    ) {}

    /**
     * GET /dashboard/trust/chain?subject_type=&subject_id=
     *
     * Returns the full evidence chain for a subject model.
     */
    public function chain(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_type' => ['required', 'string'],
            'subject_id'   => ['required', 'integer'],
        ]);

        $subject = $this->resolveSubject($validated['subject_type'], (int) $validated['subject_id']);

        if ($subject === null) {
            return response()->json(['error' => 'Subject not found.'], 404);
        }

        $entries = $this->ledgerService->getChain($subject);

        return response()->json(['entries' => $entries]);
    }

    /**
     * GET /dashboard/trust/verify?subject_type=&subject_id=
     *
     * Verifies the full chain for a subject model.
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_type' => ['required', 'string'],
            'subject_id'   => ['required', 'integer'],
        ]);

        $subject = $this->resolveSubject($validated['subject_type'], (int) $validated['subject_id']);

        if ($subject === null) {
            return response()->json(['error' => 'Subject not found.'], 404);
        }

        $intact = $this->ledgerService->verifyChain($subject);

        return response()->json(['chain_intact' => $intact]);
    }

    /**
     * GET /dashboard/trust/proof?subject_type=&subject_id=
     *
     * Returns a full compliance proof document for a subject.
     */
    public function proof(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_type' => ['required', 'string'],
            'subject_id'   => ['required', 'integer'],
        ]);

        $subject = $this->resolveSubject($validated['subject_type'], (int) $validated['subject_id']);

        if ($subject === null) {
            return response()->json(['error' => 'Subject not found.'], 404);
        }

        $proof = $this->verificationService->generateComplianceProof($subject);

        return response()->json($proof);
    }

    /**
     * POST /dashboard/trust/seal
     *
     * Seals the current chain state for the authenticated user's company.
     */
    public function seal(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;
        $seal      = $this->ledgerService->sealChain($companyId, $request->user());

        return response()->json([
            'seal_hash'   => $seal->seal_hash,
            'entry_count' => $seal->entry_count,
            'sealed_at'   => $seal->sealed_at->toIso8601String(),
        ], 201);
    }

    // -------------------------------------------------------------------------

    /**
     * Resolve a subject model from its FQCN and primary key.
     * Only allow whitelisted models for safety.
     */
    protected function resolveSubject(string $type, int $id): ?\Illuminate\Database\Eloquent\Model
    {
        $allowed = [
            'App\\Models\\Work\\ServiceJob',
            'App\\Models\\Inspection\\InspectionInstance',
            'App\\Models\\Facility\\AssetServiceEvent',
            'App\\Models\\Work\\ChecklistRun',
        ];

        if (! in_array($type, $allowed, true)) {
            return null;
        }

        return $type::find($id);
    }
}
