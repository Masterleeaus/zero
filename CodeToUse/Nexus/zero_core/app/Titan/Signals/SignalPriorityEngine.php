<?php

namespace App\Titan\Signals;

class SignalPriorityEngine
{
    public function score(array $signal): array
    {
        $severity = (string) ($signal['severity'] ?? SignalSeverity::AMBER);
        $base = match ($severity) {
            SignalSeverity::RED => 90,
            SignalSeverity::GREEN => 30,
            default => 60,
        };

        $requiresApproval = (bool) data_get($signal, 'meta.requires_approval', false);
        $amount = (int) data_get($signal, 'payload.amount_cents', 0);
        $warningCount = count((array) ($signal['validation_warnings'] ?? []));
        $errorCount = count((array) ($signal['validation_errors'] ?? []));
        $status = (string) ($signal['status'] ?? 'new');

        $score = $base;
        $score += min(20, intdiv($amount, 100000));
        $score += $requiresApproval ? 10 : 0;
        $score += min(10, $warningCount * 2);
        $score += min(20, $errorCount * 5);
        $score += in_array($status, ['awaiting-approval', 'validation-rejected', 'processing-error'], true) ? 10 : 0;
        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'band' => $score >= 85 ? 'critical' : ($score >= 65 ? 'high' : ($score >= 40 ? 'medium' : 'low')),
            'reasons' => array_values(array_filter([
                $severity === SignalSeverity::RED ? 'red_severity' : null,
                $requiresApproval ? 'approval_required' : null,
                $amount > 0 ? 'financial_signal' : null,
                $warningCount > 0 ? 'validation_warnings' : null,
                $errorCount > 0 ? 'validation_errors' : null,
                in_array($status, ['awaiting-approval', 'validation-rejected', 'processing-error'], true) ? 'actionable_status' : null,
            ])),
        ];
    }

    public function rank(array $signals): array
    {
        $scored = [];
        foreach ($signals as $signal) {
            $priority = $this->score($signal);
            $signal['priority'] = $priority;
            $scored[] = $signal;
        }

        usort($scored, function (array $a, array $b) {
            return (($b['priority']['score'] ?? 0) <=> ($a['priority']['score'] ?? 0));
        });

        return $scored;
    }
}
