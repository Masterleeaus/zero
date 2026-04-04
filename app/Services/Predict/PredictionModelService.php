<?php

declare(strict_types=1);

namespace App\Services\Predict;

use App\Services\Ai\AiCompletionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * PredictionModelService
 *
 * Routes prediction requests through the Entity/Engine AI driver abstraction.
 * All AI calls go through AiCompletionService → AnthropicService (or other engines).
 * Never calls AI APIs directly.
 *
 * Returns:
 * [
 *   'confidence'   => float,        // 0.0 – 1.0
 *   'predicted_at' => Carbon,
 *   'explanation'  => array,        // [{ signal, value, weight, contribution }]
 *   'action'       => string,
 * ]
 */
class PredictionModelService
{
    public function __construct(
        private readonly AiCompletionService $aiCompletion,
    ) {}

    /**
     * Call the prediction model for a given prediction type and signal set.
     *
     * @param  string  $predictionType  asset_failure|sla_breach|demand_surge|capacity_gap|maintenance_overdue|inspection_due
     * @param  array   $signals         Signal descriptors from PredictionSignalExtractorService
     * @param  string  $provider        'anthropic' (default) | 'openai' | 'gemini'
     * @return array{confidence: float, predicted_at: Carbon, explanation: array, action: string}
     */
    public function callPredictionModel(string $predictionType, array $signals, string $provider = 'anthropic'): array
    {
        $systemPrompt = $this->buildSystemPrompt($predictionType);
        $userContent  = $this->buildUserContent($predictionType, $signals);

        try {
            $raw = $this->aiCompletion->complete($systemPrompt, $userContent);
            return $this->parseResponse($raw, $signals);
        } catch (\Throwable $th) {
            Log::error('PredictionModelService: AI call failed', [
                'type'    => $predictionType,
                'error'   => $th->getMessage(),
            ]);

            // Fallback: derive heuristic prediction from signal weights
            return $this->heuristicFallback($signals);
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function buildSystemPrompt(string $predictionType): string
    {
        return <<<PROMPT
You are TitanPredict, a field service predictive analytics engine. Your role is to analyse operational signals and produce a JSON prediction response.

Prediction type: {$predictionType}

You MUST respond with ONLY valid JSON in this exact format:
{
  "confidence": 0.00,
  "explanation": [
    { "signal": "signal_name", "value": "signal_value", "weight": 0.00, "contribution": "low|medium|high" }
  ],
  "action": "Concise recommended action (max 200 chars)"
}

Rules:
- confidence is a decimal between 0.0000 and 1.0000
- explanation must reflect the signals provided
- action must be specific and actionable
- Respond ONLY with JSON, no preamble, no markdown
PROMPT;
    }

    private function buildUserContent(string $predictionType, array $signals): string
    {
        $signalLines = array_map(
            static fn ($s) => sprintf('  - %s: %s (weight: %s)', $s['type'], json_encode($s['value']), $s['weight']),
            $signals,
        );

        $signalText = implode("\n", $signalLines);

        return <<<CONTENT
Prediction request: {$predictionType}

Input signals:
{$signalText}

Analyse the signals and produce a prediction JSON response as instructed.
CONTENT;
    }

    private function parseResponse(string $raw, array $signals): array
    {
        // Strip markdown fences if present
        $cleaned = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $cleaned = preg_replace('/```\s*$/m', '', $cleaned ?? $raw);

        $decoded = json_decode(trim($cleaned ?? $raw), true);

        if (! is_array($decoded) || ! isset($decoded['confidence'])) {
            Log::warning('PredictionModelService: could not parse AI response, using fallback', [
                'raw' => substr($raw, 0, 500),
            ]);

            return $this->heuristicFallback($signals);
        }

        return [
            'confidence'   => min(max((float) ($decoded['confidence'] ?? 0.0), 0.0), 1.0),
            'predicted_at' => now()->addDays(7),
            'explanation'  => $decoded['explanation'] ?? [],
            'action'       => (string) ($decoded['action'] ?? 'Review the flagged signals.'),
        ];
    }

    /**
     * Heuristic fallback: compute a simple weighted average confidence from signals.
     */
    private function heuristicFallback(array $signals): array
    {
        $totalWeight = array_sum(array_column($signals, 'weight'));
        $count       = count($signals);
        $confidence  = $count > 0 ? min($totalWeight / $count, 1.0) : 0.0;

        $explanation = array_map(static function (array $s): array {
            $weight       = (float) $s['weight'];
            $contribution = match (true) {
                $weight >= 0.7 => 'high',
                $weight >= 0.4 => 'medium',
                default        => 'low',
            };

            return [
                'signal'       => $s['type'],
                'value'        => $s['value'],
                'weight'       => $weight,
                'contribution' => $contribution,
            ];
        }, $signals);

        return [
            'confidence'   => round($confidence, 4),
            'predicted_at' => now()->addDays(7),
            'explanation'  => $explanation,
            'action'       => 'Heuristic prediction: review the highest-weight signals immediately.',
        ];
    }
}
