<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Events\Money\RecommendationCreated;
use App\Models\Money\FinancialActionRecommendation;
use Illuminate\Support\Facades\Event;

/**
 * FinancialActionRecommendationService
 *
 * Creates, queues, and manages financial action recommendation payloads.
 * Recommendation-first, not execution-first.
 */
class FinancialActionRecommendationService
{
    public function recommend(
        int $companyId,
        string $actionType,
        string $title,
        string $summary,
        string $reason,
        string $severity = 'medium',
        float $confidence = 75.0,
        string $sourceService = 'system',
        array $payload = [],
        ?string $relatedType = null,
        ?int $relatedId = null,
    ): FinancialActionRecommendation {
        $rec = FinancialActionRecommendation::create([
            'company_id'        => $companyId,
            'action_type'       => $actionType,
            'title'             => $title,
            'summary'           => $summary,
            'reason'            => $reason,
            'severity'          => $severity,
            'confidence'        => $confidence,
            'source_service'    => $sourceService,
            'related_type'      => $relatedType,
            'related_id'        => $relatedId,
            'payload'           => $payload,
            'status'            => 'pending_review',
            'created_by_system' => true,
        ]);

        Event::dispatch(new RecommendationCreated($rec));

        return $rec;
    }

    public function pending(int $companyId): \Illuminate\Database\Eloquent\Collection
    {
        return FinancialActionRecommendation::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'pending_review')
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function generateFromVariance(int $companyId, array $varianceReport): array
    {
        $created = [];
        foreach ($varianceReport['lines'] ?? [] as $line) {
            if (in_array($line['risk'], ['high', 'critical'])) {
                $rec = $this->recommend(
                    companyId: $companyId,
                    actionType: 'budget_overrun_review',
                    title: 'Budget Overrun Detected: ' . ($line['line_type'] ?? 'line'),
                    summary: 'Variance of ' . ($line['variance_percent'] ?? '?') . '% detected on budget line.',
                    reason: 'Actual spend has deviated significantly from budgeted amount for line type ' . ($line['line_type'] ?? 'unknown'),
                    severity: $line['risk'] === 'critical' ? 'critical' : 'high',
                    confidence: 90.0,
                    sourceService: 'BudgetVarianceService',
                    payload: $line,
                );
                $created[] = $rec;
            }
        }
        return $created;
    }

    public function generateFromScenario(int $companyId, array $scenarioResult): ?FinancialActionRecommendation
    {
        $impact = $scenarioResult['impact'] ?? [];
        if (in_array($impact['severity'] ?? 'low', ['high', 'critical'])) {
            return $this->recommend(
                companyId: $companyId,
                actionType: 'scenario_risk_review',
                title: 'Scenario Risk: ' . ($scenarioResult['scenario_type'] ?? 'unknown'),
                summary: 'What-if scenario shows ' . ($impact['severity'] ?? 'high') . ' impact.',
                reason: 'Margin impact of ' . ($impact['margin_impact'] ?? 0) . ' simulated under scenario ' . ($scenarioResult['scenario_type'] ?? 'unknown'),
                severity: $impact['severity'] ?? 'medium',
                confidence: 70.0,
                sourceService: 'ScenarioSimulationService',
                payload: $scenarioResult,
            );
        }
        return null;
    }
}
