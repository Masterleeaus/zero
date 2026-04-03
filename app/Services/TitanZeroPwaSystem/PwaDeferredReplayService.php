<?php

namespace App\Services\TitanZeroPwaSystem;

use App\Models\TzPwaSignalIngress;
use App\Jobs\TitanPwa\PromotePwaIngressJob;
use Illuminate\Support\Facades\Log;

/**
 * PwaDeferredReplayService
 *
 * Replays failed or deferred PWA ingress items safely.
 *
 * Responsibilities:
 * - Identify ingress rows eligible for replay (failed or deferred with elapsed timer)
 * - Re-queue PromotePwaIngressJob for eligible items
 * - Surface dead-letter failures (exceeded max retries) without losing them
 * - Provide replay summary for operator visibility
 * - Support reconnect-trigger replay (client calls /pwa/sync/reconnect)
 */
class PwaDeferredReplayService
{
    /** Maximum retry attempts before marking as dead_letter */
    private const MAX_RETRIES = 5;

    /** Default deferred retry window in minutes */
    private const DEFERRED_WINDOW_MINUTES = 5;

    /**
     * Replay all eligible deferred/failed ingress items for a company.
     *
     * Returns a summary of actions taken.
     */
    public function replayForCompany(int $companyId, int $limit = 100): array
    {
        $replayed    = 0;
        $deadLetters = 0;
        $skipped     = 0;

        $eligible = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereIn('signal_stage', ['failed', 'deferred'])
            ->where('retry_count', '<', self::MAX_RETRIES)
            ->where(function ($q) {
                $q->whereNull('deferred_until')
                    ->orWhere('deferred_until', '<=', now());
            })
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        foreach ($eligible as $ingress) {
            if (! $ingress->consensus_passed) {
                // Cannot replay items that failed consensus — mark as permanently failed
                $ingress->update([
                    'signal_stage'   => 'failed',
                    'last_error_code' => 'consensus_failed_no_replay',
                    'failure_reason' => 'Replay skipped: consensus not passed on original ingestion',
                ]);
                $skipped++;
                continue;
            }

            $newRetryCount = ($ingress->retry_count ?? 0) + 1;

            // Mark as processing/deferred with next window
            $ingress->update([
                'signal_stage'   => 'pending',
                'retry_count'    => $newRetryCount,
                'deferred_until' => now()->addMinutes(self::DEFERRED_WINDOW_MINUTES * $newRetryCount),
                'last_error_code' => null,
            ]);

            PromotePwaIngressJob::dispatch($ingress->id);
            $replayed++;
        }

        // Identify dead-letter items (exceeded max retries, still not promoted)
        $deadLetterCount = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereIn('signal_stage', ['failed', 'deferred'])
            ->where('retry_count', '>=', self::MAX_RETRIES)
            ->count();

        Log::info('[PwaDeferredReplayService] Replay completed', [
            'company_id'        => $companyId,
            'replayed'          => $replayed,
            'skipped'           => $skipped,
            'dead_letter_total' => $deadLetterCount,
        ]);

        return [
            'replayed'          => $replayed,
            'skipped'           => $skipped,
            'dead_letter_total' => $deadLetterCount,
            'eligible_found'    => $eligible->count(),
        ];
    }

    /**
     * Replay deferred/failed ingress items for a specific node.
     * Used for reconnect-triggered replay.
     */
    public function replayForNode(string $nodeId, int $companyId, int $limit = 50): array
    {
        $replayed = 0;
        $skipped  = 0;

        $eligible = TzPwaSignalIngress::where('company_id', $companyId)
            ->where('node_id', $nodeId)
            ->whereIn('signal_stage', ['failed', 'deferred'])
            ->where('retry_count', '<', self::MAX_RETRIES)
            ->where(function ($q) {
                $q->whereNull('deferred_until')
                    ->orWhere('deferred_until', '<=', now());
            })
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        foreach ($eligible as $ingress) {
            if (! $ingress->consensus_passed) {
                $skipped++;
                continue;
            }

            $newRetryCount = ($ingress->retry_count ?? 0) + 1;

            $ingress->update([
                'signal_stage'   => 'pending',
                'retry_count'    => $newRetryCount,
                'deferred_until' => now()->addMinutes(self::DEFERRED_WINDOW_MINUTES * $newRetryCount),
                'last_error_code' => null,
            ]);

            PromotePwaIngressJob::dispatch($ingress->id);
            $replayed++;
        }

        return [
            'node_id'  => $nodeId,
            'replayed' => $replayed,
            'skipped'  => $skipped,
        ];
    }

    /**
     * Prune dead-letter items older than the given number of days.
     * Items are marked 'abandoned' rather than deleted to preserve auditability.
     */
    public function pruneDeadLetters(int $companyId, int $olderThanDays = 30): int
    {
        return TzPwaSignalIngress::where('company_id', $companyId)
            ->whereIn('signal_stage', ['failed', 'deferred'])
            ->where('retry_count', '>=', self::MAX_RETRIES)
            ->where('created_at', '<', now()->subDays($olderThanDays))
            ->update([
                'signal_stage'    => 'abandoned',
                'last_error_code' => 'pruned_dead_letter',
            ]);
    }

    /**
     * Get dead-letter summary for operator visibility.
     */
    public function deadLetterSummary(int $companyId): array
    {
        $deadLetters = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereIn('signal_stage', ['failed', 'deferred'])
            ->where('retry_count', '>=', self::MAX_RETRIES)
            ->select('node_id', 'signal_key', 'failure_reason', 'last_error_code', 'created_at', 'retry_count')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return [
            'count'    => $deadLetters->count(),
            'items'    => $deadLetters->toArray(),
            'max_retries' => self::MAX_RETRIES,
        ];
    }
}
