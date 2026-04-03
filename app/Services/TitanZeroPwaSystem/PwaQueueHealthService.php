<?php

namespace App\Services\TitanZeroPwaSystem;

use App\Models\TzPwaDevice;
use App\Models\TzPwaSignalIngress;
use App\Models\TzPwaStagedArtifact;
use Illuminate\Support\Facades\DB;

/**
 * PwaQueueHealthService
 *
 * Provides queue health summaries for the operator diagnostics surface.
 *
 * Responsibilities:
 * - Compute per-company and per-device queue backlog metrics
 * - Surface dead-letter / stuck items
 * - Identify stale devices and failed nodes
 * - Provide aggregate conflict type distributions
 */
class PwaQueueHealthService
{
    /**
     * Return a comprehensive queue health summary for a company.
     */
    public function summary(int $companyId): array
    {
        $ingress = $this->ingressHealth($companyId);
        $devices = $this->deviceHealth($companyId);
        $staging = $this->stagingHealth($companyId);
        $conflicts = $this->conflictSummary($companyId);

        return [
            'ingress'   => $ingress,
            'devices'   => $devices,
            'staging'   => $staging,
            'conflicts' => $conflicts,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Signal ingress queue health breakdown.
     */
    public function ingressHealth(int $companyId): array
    {
        $stages = TzPwaSignalIngress::where('company_id', $companyId)
            ->select('signal_stage', DB::raw('count(*) as count'))
            ->groupBy('signal_stage')
            ->pluck('count', 'signal_stage')
            ->toArray();

        $deferredReady = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereIn('signal_stage', ['failed', 'deferred'])
            ->where(function ($q) {
                $q->whereNull('deferred_until')
                    ->orWhere('deferred_until', '<=', now());
            })
            ->count();

        $deadLetterCount = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereIn('signal_stage', ['failed', 'deferred'])
            ->where('retry_count', '>=', 5)
            ->count();

        $avgRetries = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereIn('signal_stage', ['failed', 'deferred'])
            ->avg('retry_count') ?? 0.0;

        $oldestPending = TzPwaSignalIngress::where('company_id', $companyId)
            ->where('signal_stage', 'pending')
            ->min('created_at');

        return [
            'by_stage'         => $stages,
            'deferred_ready'   => $deferredReady,
            'dead_letter_count' => $deadLetterCount,
            'avg_retries'      => round((float) $avgRetries, 2),
            'oldest_pending'   => $oldestPending,
            'total'            => array_sum($stages),
        ];
    }

    /**
     * Device-level health metrics.
     */
    public function deviceHealth(int $companyId): array
    {
        $staleThreshold = now()->subHours(24);
        $criticalThreshold = now()->subHours(72);

        $totalDevices = TzPwaDevice::where('company_id', $companyId)->count();
        $staleDevices = TzPwaDevice::where('company_id', $companyId)
            ->where(fn ($q) => $q->where('last_seen_at', '<', $staleThreshold)->orWhereNull('last_seen_at'))
            ->count();
        $criticalDevices = TzPwaDevice::where('company_id', $companyId)
            ->where(fn ($q) => $q->where('last_seen_at', '<', $criticalThreshold)->orWhereNull('last_seen_at'))
            ->count();

        $tierBreakdown = TzPwaDevice::where('company_id', $companyId)
            ->select('capability_tier', DB::raw('count(*) as count'))
            ->groupBy('capability_tier')
            ->pluck('count', 'capability_tier')
            ->toArray();

        $versionBreakdown = TzPwaDevice::where('company_id', $companyId)
            ->select('runtime_version', DB::raw('count(*) as count'))
            ->groupBy('runtime_version')
            ->pluck('count', 'runtime_version')
            ->toArray();

        // Per-device queue backlog (top 10 highest)
        $backlogTop = TzPwaDevice::where('company_id', $companyId)
            ->where('queue_backlog', '>', 0)
            ->orderByDesc('queue_backlog')
            ->limit(10)
            ->get(['node_id', 'device_label', 'platform', 'queue_backlog', 'last_seen_at'])
            ->map(fn ($d) => [
                'node_id'      => $d->node_id,
                'device_label' => $d->device_label,
                'platform'     => $d->platform,
                'queue_backlog' => $d->queue_backlog,
                'last_seen_at' => $d->last_seen_at?->toIso8601String(),
            ]);

        return [
            'total'             => $totalDevices,
            'stale_24h'         => $staleDevices,
            'critical_72h'      => $criticalDevices,
            'tier_breakdown'    => $tierBreakdown,
            'version_breakdown' => $versionBreakdown,
            'backlog_top'       => $backlogTop,
        ];
    }

    /**
     * Staged artifact health summary.
     */
    public function stagingHealth(int $companyId): array
    {
        $stages = TzPwaStagedArtifact::where('company_id', $companyId)
            ->select('artifact_stage', DB::raw('count(*) as count'))
            ->groupBy('artifact_stage')
            ->pluck('count', 'artifact_stage')
            ->toArray();

        $byType = TzPwaStagedArtifact::where('company_id', $companyId)
            ->select('artifact_type', DB::raw('count(*) as count'))
            ->groupBy('artifact_type')
            ->pluck('count', 'artifact_type')
            ->toArray();

        $failedArtifacts = TzPwaStagedArtifact::where('company_id', $companyId)
            ->where('artifact_stage', 'failed')
            ->count();

        return [
            'by_stage'        => $stages,
            'by_type'         => $byType,
            'failed_count'    => $failedArtifacts,
            'total'           => array_sum($stages),
        ];
    }

    /**
     * Conflict event distribution summary.
     */
    public function conflictSummary(int $companyId): array
    {
        $conflicts = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereNotNull('conflict_type')
            ->select('conflict_type', DB::raw('count(*) as count'))
            ->groupBy('conflict_type')
            ->pluck('count', 'conflict_type')
            ->toArray();

        $unresolvedConflicts = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereNotNull('conflict_type')
            ->whereNull('conflict_resolved_at')
            ->count();

        $recentConflicts = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereNotNull('conflict_type')
            ->where('created_at', '>=', now()->subHours(24))
            ->select('node_id', 'signal_key', 'conflict_type', 'failure_reason', 'created_at', 'ingest_status')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'node_id'       => $r->node_id,
                'signal_key'    => $r->signal_key,
                'conflict_type' => $r->conflict_type,
                'reason'        => $r->failure_reason,
                'at'            => $r->created_at?->toIso8601String(),
                'status'        => $r->ingest_status,
            ]);

        return [
            'by_type'            => $conflicts,
            'unresolved_count'   => $unresolvedConflicts,
            'recent_24h'         => $recentConflicts,
            'total'              => array_sum($conflicts),
        ];
    }

    /**
     * Update the queue_backlog field on a device based on current DB state.
     */
    public function refreshDeviceBacklog(string $nodeId, int $companyId): void
    {
        $count = TzPwaSignalIngress::where('node_id', $nodeId)
            ->where('company_id', $companyId)
            ->where('signal_stage', 'pending')
            ->count();

        TzPwaDevice::where('node_id', $nodeId)
            ->where('company_id', $companyId)
            ->update(['queue_backlog' => $count]);
    }
}
