<?php

namespace App\Services\TitanZeroPwaSystem;

use App\Models\TzPwaDevice;
use Illuminate\Support\Facades\Log;

/**
 * Manages node trust classification, promotion/demotion, throttling, and audit logging.
 *
 * Trust hierarchy (ascending):
 *   untrusted → provisional → trusted → verified
 *
 * Rules:
 * - New devices register as 'provisional'.
 * - Repeated signature failures downgrade to 'untrusted' and set rate-limiting.
 * - Verified nodes are manually promoted by operators.
 * - Suspicious nodes (>N failures in window) are throttled.
 */
class NodeTrustService
{
    public const LEVELS = ['untrusted', 'provisional', 'trusted', 'verified'];

    /** Signature failures before trust downgrade */
    private const DOWNGRADE_THRESHOLD = 5;

    /** Signature failures before rate-limiting kicks in */
    private const THROTTLE_THRESHOLD = 3;

    /**
     * Gate an ingest request: return true if node is allowed to submit signals.
     */
    public function gate(TzPwaDevice $device): array
    {
        $level = $device->trust_level ?? 'untrusted';

        if ($level === 'untrusted') {
            return ['allowed' => false, 'reason' => 'Node is untrusted', 'code' => 'untrusted'];
        }

        if ($device->is_rate_limited) {
            return ['allowed' => false, 'reason' => 'Node is rate-limited due to repeated failures', 'code' => 'rate_limited'];
        }

        return ['allowed' => true, 'reason' => 'Node cleared trust gate', 'code' => 'ok'];
    }

    /**
     * Record a successful signal validation for a node.
     * Resets failure counters if they had accumulated.
     */
    public function recordSuccess(TzPwaDevice $device): void
    {
        $failures = (int) ($device->signature_failures ?? 0);

        if ($failures > 0) {
            $device->update([
                'signature_failures' => 0,
                'is_rate_limited'    => false,
                'last_failure_at'    => null,
            ]);
        }
    }

    /**
     * Record a signature failure, applying throttle/downgrade logic.
     */
    public function recordFailure(TzPwaDevice $device, string $reason = 'signature_failure'): void
    {
        $failures = (int) ($device->signature_failures ?? 0) + 1;

        $updates = [
            'signature_failures' => $failures,
            'last_failure_at'    => now(),
        ];

        if ($failures >= self::DOWNGRADE_THRESHOLD) {
            $updates['trust_level']   = 'untrusted';
            $updates['is_rate_limited'] = true;

            Log::warning('[NodeTrustService] Node downgraded to untrusted', [
                'node_id'  => $device->node_id,
                'failures' => $failures,
                'reason'   => $reason,
            ]);
        } elseif ($failures >= self::THROTTLE_THRESHOLD) {
            $updates['is_rate_limited'] = true;

            Log::warning('[NodeTrustService] Node rate-limited', [
                'node_id'  => $device->node_id,
                'failures' => $failures,
            ]);
        }

        $device->update($updates);
    }

    /**
     * Promote a node to a higher trust level (operator action).
     */
    public function promote(TzPwaDevice $device, string $targetLevel, ?int $operatorUserId = null): array
    {
        if (! in_array($targetLevel, self::LEVELS, true)) {
            return ['ok' => false, 'reason' => "Invalid trust level: {$targetLevel}"];
        }

        $currentIdx = array_search($device->trust_level, self::LEVELS, true);
        $targetIdx  = array_search($targetLevel, self::LEVELS, true);

        if ($targetIdx <= $currentIdx) {
            return ['ok' => false, 'reason' => "Cannot promote to same or lower level ({$targetLevel})"];
        }

        $device->update([
            'trust_level'        => $targetLevel,
            'is_rate_limited'    => false,
            'signature_failures' => 0,
        ]);

        Log::info('[NodeTrustService] Node trust promoted', [
            'node_id'    => $device->node_id,
            'from'       => $device->getOriginal('trust_level'),
            'to'         => $targetLevel,
            'by_user_id' => $operatorUserId,
        ]);

        return ['ok' => true, 'trust_level' => $targetLevel];
    }

    /**
     * Demote a node to a lower trust level (operator action or automated).
     */
    public function demote(TzPwaDevice $device, string $targetLevel, string $reason = 'manual_demotion'): array
    {
        if (! in_array($targetLevel, self::LEVELS, true)) {
            return ['ok' => false, 'reason' => "Invalid trust level: {$targetLevel}"];
        }

        $device->update(['trust_level' => $targetLevel]);

        Log::warning('[NodeTrustService] Node trust demoted', [
            'node_id' => $device->node_id,
            'to'      => $targetLevel,
            'reason'  => $reason,
        ]);

        return ['ok' => true, 'trust_level' => $targetLevel];
    }

    /**
     * Clear rate-limit state for a node (operator reset).
     */
    public function clearRateLimit(TzPwaDevice $device): void
    {
        $device->update([
            'is_rate_limited'    => false,
            'signature_failures' => 0,
            'last_failure_at'    => null,
        ]);

        Log::info('[NodeTrustService] Node rate-limit cleared', ['node_id' => $device->node_id]);
    }

    /**
     * Return a summary of trust posture for a device.
     */
    public function summary(TzPwaDevice $device): array
    {
        return [
            'node_id'            => $device->node_id,
            'trust_level'        => $device->trust_level,
            'is_rate_limited'    => (bool) $device->is_rate_limited,
            'signature_failures' => (int) ($device->signature_failures ?? 0),
            'last_failure_at'    => $device->last_failure_at?->toIso8601String(),
        ];
    }
}
