<?php

namespace App\Services\TitanCoreConsensus;

class TriCoreConsensus
{
    const TIMESTAMP_DRIFT_TOLERANCE = 300;
    const MIN_CONFIDENCE_SCORE = 0.3;
    const REQUIRED_PAYLOAD_KEYS = ['signal_key', 'payload'];
    const TRUST_LEVELS = [
        'untrusted'   => 0,
        'provisional' => 1,
        'trusted'     => 2,
        'verified'    => 3,
    ];
    const VALID_SIGNAL_STAGES = ['draft', 'pending', 'active', 'complete', 'error'];

    /**
     * Run all consensus checks and return a structured result.
     */
    public function validate(array $signal, ?object $device = null): array
    {
        $checks = [];
        $score = $this->score($signal, $device);

        // Check 1: Timestamp sanity
        $checks['timestamp_sanity'] = $this->checkTimestamp($signal);

        // Check 2: Payload structure
        $checks['payload_structure'] = $this->checkPayloadStructure($signal);

        // Check 3: Authorization scope (device trust >= provisional)
        $checks['authorization_scope'] = $this->checkAuthorizationScope($signal, $device);

        // Check 4: State transition validity
        $checks['state_transition'] = $this->checkStateTransition($signal);

        // Check 5: Confidence score threshold
        $checks['confidence_score'] = [
            'passed' => $score >= self::MIN_CONFIDENCE_SCORE,
            'score'  => $score,
            'reason' => $score >= self::MIN_CONFIDENCE_SCORE
                ? 'Score meets threshold'
                : 'Score below minimum threshold of '.self::MIN_CONFIDENCE_SCORE,
        ];

        $allPassed = collect($checks)->every(fn ($check) => $check['passed'] === true);

        $failedCheck = collect($checks)->first(fn ($check) => $check['passed'] === false);
        $reason = $allPassed ? 'All consensus checks passed' : ($failedCheck['reason'] ?? 'Consensus check failed');

        return [
            'passed' => $allPassed,
            'score'  => $score,
            'reason' => $reason,
            'checks' => $checks,
        ];
    }

    /**
     * Compute a confidence score from 0.0 to 1.0.
     */
    public function score(array $signal, ?object $device = null): float
    {
        $score = 0.0;

        // +0.3 if signal has a signature
        if (! empty($signal['signature'])) {
            $score += 0.3;
        }

        // +0.3 if device is trusted or verified
        if ($device !== null) {
            $trustValue = self::TRUST_LEVELS[$device->trust_level ?? 'untrusted'] ?? 0;
            if ($trustValue >= self::TRUST_LEVELS['trusted']) {
                $score += 0.3;
            }
        }

        // +0.2 if timestamp is within 60s
        if (isset($signal['timestamp'])) {
            $ts = is_numeric($signal['timestamp'])
                ? (int) $signal['timestamp']
                : strtotime($signal['timestamp']);
            if ($ts && abs(time() - $ts) <= 60) {
                $score += 0.2;
            }
        }

        // +0.2 if payload has expected structure
        if ($this->checkPayloadStructure($signal)['passed']) {
            $score += 0.2;
        }

        return round(min($score, 1.0), 4);
    }

    /**
     * Determine if a signal is authorized based on device trust level.
     */
    public function isAuthorized(array $signal, ?object $device = null): bool
    {
        return $this->checkAuthorizationScope($signal, $device)['passed'];
    }

    private function checkTimestamp(array $signal): array
    {
        if (empty($signal['timestamp'])) {
            return ['passed' => false, 'reason' => 'Missing timestamp'];
        }

        $ts = is_numeric($signal['timestamp'])
            ? (int) $signal['timestamp']
            : strtotime($signal['timestamp']);

        if (! $ts) {
            return ['passed' => false, 'reason' => 'Unparseable timestamp'];
        }

        $drift = abs(time() - $ts);
        $passed = $drift <= self::TIMESTAMP_DRIFT_TOLERANCE;

        return [
            'passed' => $passed,
            'reason' => $passed
                ? 'Timestamp within drift tolerance'
                : "Timestamp drift of {$drift}s exceeds tolerance of ".self::TIMESTAMP_DRIFT_TOLERANCE.'s',
        ];
    }

    private function checkPayloadStructure(array $signal): array
    {
        $missing = [];
        foreach (self::REQUIRED_PAYLOAD_KEYS as $key) {
            if (! array_key_exists($key, $signal)) {
                $missing[] = $key;
            }
        }

        return [
            'passed' => empty($missing),
            'reason' => empty($missing)
                ? 'All required payload keys present'
                : 'Missing required keys: '.implode(', ', $missing),
        ];
    }

    private function checkAuthorizationScope(array $signal, ?object $device = null): array
    {
        if ($device === null) {
            return ['passed' => false, 'reason' => 'No device record found; cannot authorize'];
        }

        $trustValue = self::TRUST_LEVELS[$device->trust_level ?? 'untrusted'] ?? 0;
        $required = self::TRUST_LEVELS['provisional'];
        $passed = $trustValue >= $required;

        return [
            'passed' => $passed,
            'reason' => $passed
                ? "Device trust level '{$device->trust_level}' meets minimum requirement"
                : "Device trust level '{$device->trust_level}' is below provisional",
        ];
    }

    private function checkStateTransition(array $signal): array
    {
        $stage = $signal['signal_stage'] ?? null;

        if ($stage === null) {
            // No stage provided — treat as acceptable (defaults to draft)
            return ['passed' => true, 'reason' => 'No signal_stage provided; defaulting to draft'];
        }

        $passed = in_array($stage, self::VALID_SIGNAL_STAGES, true);

        return [
            'passed' => $passed,
            'reason' => $passed
                ? "Signal stage '{$stage}' is valid"
                : "Invalid signal_stage '{$stage}'; must be one of: ".implode(', ', self::VALID_SIGNAL_STAGES),
        ];
    }
}
