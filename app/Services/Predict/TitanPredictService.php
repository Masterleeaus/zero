<?php

declare(strict_types=1);

namespace App\Services\Predict;

use App\Events\Predict\HighConfidencePrediction;
use App\Events\Predict\PredictionFeedbackRecorded;
use App\Events\Predict\PredictionGenerated;
use App\Events\Predict\PredictionTriggered;
use App\Models\Facility\SiteAsset;
use App\Models\Predict\Prediction;
use App\Models\Predict\PredictionOutcome;
use App\Models\Predict\PredictionSignal;
use App\Models\User;
use App\Models\Work\ServiceAgreement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TitanPredictService
{
    public const HIGH_CONFIDENCE_THRESHOLD = 0.85;

    public function __construct(
        private readonly PredictionSignalExtractorService $extractor,
        private readonly PredictionModelService           $modelService,
    ) {}

    // ── Core prediction generators ─────────────────────────────────────────────

    public function generateAssetFailurePrediction(SiteAsset $asset): Prediction
    {
        $signals  = $this->extractor->extractAssetSignals($asset);
        $result   = $this->modelService->callPredictionModel('asset_failure', $signals);

        return $this->persistPrediction(
            companyId    : $asset->company_id,
            type         : 'asset_failure',
            subject      : $asset,
            signals      : $signals,
            result       : $result,
            expiresInDays: 30,
        );
    }

    public function generateSLARiskPrediction(ServiceAgreement $agreement): Prediction
    {
        $signals = $this->extractor->extractSLASignals($agreement);
        $result  = $this->modelService->callPredictionModel('sla_breach', $signals);

        return $this->persistPrediction(
            companyId    : $agreement->company_id,
            type         : 'sla_breach',
            subject      : $agreement,
            signals      : $signals,
            result       : $result,
            expiresInDays: 14,
        );
    }

    /**
     * Generate demand forecasts for a company over a given period.
     *
     * @return Collection<int, Prediction>
     */
    public function generateDemandForecast(int $companyId, Carbon $forPeriod): Collection
    {
        $serviceTypes = ['maintenance', 'repair', 'installation', ''];

        $predictions = collect();

        foreach ($serviceTypes as $type) {
            $signals = $this->extractor->extractJobHistorySignals($companyId, $type);
            $result  = $this->modelService->callPredictionModel('demand_surge', $signals);

            $prediction = $this->persistPrediction(
                companyId    : $companyId,
                type         : 'demand_surge',
                subject      : null,
                signals      : $signals,
                result       : $result,
                expiresInDays: 7,
            );

            $predictions->push($prediction);
        }

        return $predictions;
    }

    public function generateCapacityGapPrediction(int $companyId, Carbon $forDate): Prediction
    {
        $signals = $this->extractor->extractCapacitySignals($companyId, $forDate);
        $result  = $this->modelService->callPredictionModel('capacity_gap', $signals);

        return $this->persistPrediction(
            companyId    : $companyId,
            type         : 'capacity_gap',
            subject      : null,
            signals      : $signals,
            result       : $result,
            expiresInDays: 3,
        );
    }

    // ── Lifecycle management ───────────────────────────────────────────────────

    public function dismissPrediction(Prediction $prediction, User $user): void
    {
        $prediction->update([
            'status'       => 'dismissed',
            'dismissed_by' => $user->id,
            'dismissed_at' => now(),
        ]);
    }

    public function recordOutcome(Prediction $prediction, bool $occurred, ?Carbon $at = null): PredictionOutcome
    {
        $outcome = PredictionOutcome::updateOrCreate(
            ['prediction_id' => $prediction->id],
            [
                'outcome_occurred' => $occurred,
                'outcome_at'       => $at ?? now(),
                'recorded_by'      => null,
            ],
        );

        if ($occurred) {
            $prediction->update(['status' => 'triggered']);
            PredictionTriggered::dispatch($prediction);
        }

        PredictionFeedbackRecorded::dispatch($prediction, $outcome);

        return $outcome;
    }

    public function getActivePredictions(int $companyId, ?string $type = null): Collection
    {
        return Prediction::forCompany($companyId)
            ->active()
            ->notExpired()
            ->when($type !== null, static fn ($q) => $q->ofType($type))
            ->with(['signals', 'outcome'])
            ->orderByDesc('confidence_score')
            ->get();
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function persistPrediction(
        int     $companyId,
        string  $type,
        ?object $subject,
        array   $signals,
        array   $result,
        int     $expiresInDays = 30,
    ): Prediction {
        return DB::transaction(function () use ($companyId, $type, $subject, $signals, $result, $expiresInDays): Prediction {
            $prediction = Prediction::create([
                'company_id'       => $companyId,
                'prediction_type'  => $type,
                'subject_type'     => $subject !== null ? get_class($subject) : null,
                'subject_id'       => $subject?->id,
                'confidence_score' => $result['confidence'],
                'predicted_at'     => $result['predicted_at'],
                'generated_at'     => now(),
                'expires_at'       => now()->addDays($expiresInDays),
                'status'           => 'active',
                'recommended_action' => $result['action'],
                'explanation_trace'  => $result['explanation'],
                'model_provider'   => 'anthropic',
                'model_id'         => 'claude-3-haiku-20240307',
            ]);

            // Persist signals
            foreach ($signals as $signal) {
                PredictionSignal::create([
                    'prediction_id'     => $prediction->id,
                    'signal_type'       => $signal['type'],
                    'signal_source_type'=> $subject !== null ? get_class($subject) : null,
                    'signal_source_id'  => $subject?->id,
                    'signal_value'      => ['raw' => $signal['value']],
                    'weight'            => $signal['weight'],
                    'recorded_at'       => now(),
                ]);
            }

            // Dispatch events
            PredictionGenerated::dispatch($prediction);

            if ((float) $prediction->confidence_score >= self::HIGH_CONFIDENCE_THRESHOLD) {
                HighConfidencePrediction::dispatch($prediction);
            }

            return $prediction;
        });
    }
}
