<?php

declare(strict_types=1);

namespace App\Http\Controllers\Predict;

use App\Http\Controllers\Core\CoreController;
use App\Models\Facility\SiteAsset;
use App\Models\Predict\Prediction;
use App\Models\Work\ServiceAgreement;
use App\Services\Predict\TitanPredictService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TitanPredictController extends CoreController
{
    public function __construct(private readonly TitanPredictService $predictService) {}

    /**
     * List active predictions for the authenticated user's company.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()?->company_id;

        if (! $companyId) {
            return response()->json(['error' => 'Company not resolved.'], 422);
        }

        $type        = $request->query('type');
        $predictions = $this->predictService->getActivePredictions($companyId, $type ?: null);

        return response()->json(['data' => $predictions]);
    }

    /**
     * Generate (or refresh) an asset failure prediction for a specific asset.
     */
    public function asset(Request $request, int $assetId): JsonResponse
    {
        $asset = SiteAsset::find($assetId);

        if (! $asset) {
            return response()->json(['error' => 'Asset not found.'], 404);
        }

        $prediction = $this->predictService->generateAssetFailurePrediction($asset);

        return response()->json(['data' => $prediction->load('signals')]);
    }

    /**
     * Generate (or refresh) a SLA risk prediction for a service agreement.
     */
    public function agreement(Request $request, int $agreementId): JsonResponse
    {
        $agreement = ServiceAgreement::find($agreementId);

        if (! $agreement) {
            return response()->json(['error' => 'Agreement not found.'], 404);
        }

        $prediction = $this->predictService->generateSLARiskPrediction($agreement);

        return response()->json(['data' => $prediction->load('signals')]);
    }

    /**
     * Generate a capacity gap prediction for today + 1 day.
     */
    public function capacity(Request $request): JsonResponse
    {
        $companyId = $request->user()?->company_id;

        if (! $companyId) {
            return response()->json(['error' => 'Company not resolved.'], 422);
        }

        $forDate    = Carbon::parse($request->query('date') ?? now()->addDay()->toDateString());
        $prediction = $this->predictService->generateCapacityGapPrediction($companyId, $forDate);

        return response()->json(['data' => $prediction->load('signals')]);
    }

    /**
     * Dismiss a prediction.
     */
    public function dismiss(Request $request, int $predictionId): JsonResponse
    {
        $prediction = Prediction::find($predictionId);

        if (! $prediction) {
            return response()->json(['error' => 'Prediction not found.'], 404);
        }

        $this->predictService->dismissPrediction($prediction, $request->user());

        return response()->json(['ok' => true]);
    }

    /**
     * Record outcome feedback for a prediction.
     */
    public function feedback(Request $request, int $predictionId): JsonResponse
    {
        $prediction = Prediction::find($predictionId);

        if (! $prediction) {
            return response()->json(['error' => 'Prediction not found.'], 404);
        }

        $request->validate([
            'outcome_occurred' => ['required', 'boolean'],
            'outcome_at'       => ['nullable', 'date'],
            'feedback_notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $at      = $request->input('outcome_at') ? Carbon::parse($request->input('outcome_at')) : null;
        $outcome = $this->predictService->recordOutcome($prediction, (bool) $request->input('outcome_occurred'), $at);

        if ($request->input('feedback_notes')) {
            $outcome->update(['feedback_notes' => $request->input('feedback_notes')]);
        }

        return response()->json(['data' => $outcome]);
    }
}
