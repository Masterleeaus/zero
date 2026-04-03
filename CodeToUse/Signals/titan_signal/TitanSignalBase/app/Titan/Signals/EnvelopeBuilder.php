<?php

namespace App\Titan\Signals;

class EnvelopeBuilder
{
    public function __construct(
        protected ?SignalPriorityEngine $priorityEngine = null,
    ) {
        $this->priorityEngine ??= app(SignalPriorityEngine::class);
    }

    public function build(array $context = []): array
    {
        $signals = $this->priorityEngine->rank(array_values($context['signals'] ?? []));
        $severityCounts = ['GREEN' => 0, 'AMBER' => 0, 'RED' => 0];
        $requiresApproval = 0;
        $statusCounts = [];
        $approvalQueue = [];
        $topSignals = [];

        foreach ($signals as $index => $signal) {
            $severity = $signal['severity'] ?? SignalSeverity::AMBER;
            $severityCounts[$severity] = ($severityCounts[$severity] ?? 0) + 1;
            $status = $signal['status'] ?? 'unknown';
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
            if ((bool) data_get($signal, 'meta.requires_approval', false)) {
                $requiresApproval++;
                $approvalQueue[] = [
                    'signal_id' => $signal['id'] ?? null,
                    'next_approver' => data_get($signal, 'meta.next_approver'),
                ];
            }
            if ($index < 5) {
                $topSignals[] = [
                    'signal_id' => $signal['id'] ?? null,
                    'type' => $signal['type'] ?? null,
                    'status' => $signal['status'] ?? null,
                    'priority' => $signal['priority'] ?? null,
                ];
            }
        }

        $headline = $topSignals[0] ?? null;

        return [
            'id' => $context['id'] ?? ('env-'.str_replace('.', '-', uniqid('', true))),
            'company_id' => $context['company_id'] ?? null,
            'team_id' => $context['team_id'] ?? null,
            'actor_id' => $context['actor_id'] ?? $context['user_id'] ?? null,
            'origin' => $context['origin'] ?? 'server',
            'summary' => $context['summary'] ?? ('Signal envelope with '.count($signals).' signals'),
            'headline' => $headline,
            'signals' => $signals,
            'top_signals' => $topSignals,
            'meta' => array_merge([
                'signal_count' => count($signals),
                'severity_counts' => $severityCounts,
                'status_counts' => $statusCounts,
                'requires_approval_count' => $requiresApproval,
                'approval_queue' => $approvalQueue,
            ], $context['meta'] ?? []),
            'risk' => [
                'priority' => $severityCounts['RED'] > 0 ? 'high' : ($severityCounts['AMBER'] > 0 ? 'medium' : 'low'),
                'approval_pressure' => $requiresApproval > 0 ? 'elevated' : 'clear',
                'top_priority_band' => data_get($headline, 'priority.band', 'low'),
            ],
            'timeline_hint' => [
                'latest_signal_at' => $signals[0]['created_at'] ?? $signals[0]['timestamp'] ?? null,
                'oldest_signal_at' => $signals[count($signals) - 1]['created_at'] ?? $signals[count($signals) - 1]['timestamp'] ?? null,
            ],
            'timestamp' => $context['timestamp'] ?? now()->toIso8601String(),
        ];
    }
}
