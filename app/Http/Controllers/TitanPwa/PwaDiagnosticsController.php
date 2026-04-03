<?php

namespace App\Http\Controllers\TitanPwa;

use App\Http\Controllers\Controller;
use App\Models\TzPwaDevice;
use App\Models\TzPwaSignalIngress;
use App\Services\TitanZeroPwaSystem\NodeTrustService;
use App\Services\TitanZeroPwaSystem\PwaDeferredReplayService;
use App\Services\TitanZeroPwaSystem\PwaQueueHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PwaDiagnosticsController extends Controller
{
    public function __construct(
        protected NodeTrustService $trustService,
        protected PwaQueueHealthService $queueHealthService,
        protected PwaDeferredReplayService $replayService,
    ) {
        $this->middleware(['auth']);
    }

    /**
     * Render the admin diagnostics view.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $user->company_id;

        $stats = $this->compileStats($companyId);
        $queueHealth = $this->queueHealthService->summary($companyId);

        return view('default.panel.admin.pwa.diagnostics', compact('stats', 'queueHealth'));
    }

    /**
     * JSON endpoint for operator tools or AJAX refresh.
     */
    public function stats(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $user->company_id;

        return response()->json($this->compileStats($companyId));
    }

    /**
     * JSON: full queue health summary.
     */
    public function queueHealth(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json(
            $this->queueHealthService->summary((int) $user->company_id)
        );
    }

    /**
     * JSON: conflict inspection — recent conflict events for operator review.
     */
    public function conflicts(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $user->company_id;

        $perPage = min((int) $request->query('per_page', 50), 200);

        $conflicts = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereNotNull('conflict_type')
            ->select([
                'id', 'node_id', 'signal_key', 'conflict_type',
                'ingest_status', 'failure_reason', 'last_error_code',
                'retry_count', 'conflict_resolved_at', 'server_received_at',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($conflicts);
    }

    /**
     * Promote a node's trust level (operator action).
     */
    public function promoteNode(Request $request): JsonResponse
    {
        $request->validate([
            'node_id'      => 'required|string',
            'trust_level'  => 'required|string|in:provisional,trusted,verified',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $device = TzPwaDevice::where('node_id', $request->node_id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $result = $this->trustService->promote($device, $request->trust_level, $user->id);

        return response()->json($result);
    }

    /**
     * Reset a node's rate-limit (operator action).
     */
    public function clearRateLimit(Request $request): JsonResponse
    {
        $request->validate(['node_id' => 'required|string']);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $device = TzPwaDevice::where('node_id', $request->node_id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $this->trustService->clearRateLimit($device);

        return response()->json(['ok' => true, 'node_id' => $device->node_id]);
    }

    /**
     * Operator-triggered deferred replay for a company (or specific node).
     */
    public function triggerReplay(Request $request): JsonResponse
    {
        $request->validate([
            'node_id' => 'sometimes|nullable|string',
            'limit'   => 'sometimes|integer|min:1|max:500',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $user->company_id;
        $limit = (int) ($request->input('limit', 100));

        $nodeId = $request->input('node_id');

        if ($nodeId) {
            $result = $this->replayService->replayForNode($nodeId, $companyId, $limit);
        } else {
            $result = $this->replayService->replayForCompany($companyId, $limit);
        }

        return response()->json(array_merge(['ok' => true], $result));
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function compileStats(int $companyId): array
    {
        $totalDevices       = TzPwaDevice::where('company_id', $companyId)->count();
        $rateLimitedDevices = TzPwaDevice::where('company_id', $companyId)->where('is_rate_limited', true)->count();

        $trustBreakdown = TzPwaDevice::where('company_id', $companyId)
            ->select('trust_level', DB::raw('count(*) as count'))
            ->groupBy('trust_level')
            ->pluck('count', 'trust_level')
            ->toArray();

        $tierBreakdown = TzPwaDevice::where('company_id', $companyId)
            ->select('capability_tier', DB::raw('count(*) as count'))
            ->groupBy('capability_tier')
            ->pluck('count', 'capability_tier')
            ->toArray();

        $totalIngress    = TzPwaSignalIngress::where('company_id', $companyId)->count();
        $pendingIngress  = TzPwaSignalIngress::where('company_id', $companyId)->where('signal_stage', 'pending')->count();
        $promotedIngress = TzPwaSignalIngress::where('company_id', $companyId)->where('signal_stage', 'promoted')->count();
        $failedIngress   = TzPwaSignalIngress::where('company_id', $companyId)->where('signal_stage', 'failed')->count();
        $deferredIngress = TzPwaSignalIngress::where('company_id', $companyId)->where('signal_stage', 'deferred')->count();

        $conflictCount = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereNotNull('conflict_type')
            ->whereNull('conflict_resolved_at')
            ->count();

        $deadLetterCount = TzPwaSignalIngress::where('company_id', $companyId)
            ->whereIn('signal_stage', ['failed', 'deferred'])
            ->where('retry_count', '>=', 5)
            ->count();

        $lastPromotionAt = TzPwaSignalIngress::where('company_id', $companyId)
            ->where('signal_stage', 'promoted')
            ->max('processed_at');

        $recentDevices = TzPwaDevice::where('company_id', $companyId)
            ->withCount([
                'signalIngresses as signal_count',
                'signalIngresses as pending_count' => fn ($q) => $q->where('signal_stage', 'pending'),
                'signalIngresses as failed_count'  => fn ($q) => $q->where('signal_stage', 'failed'),
            ])
            ->orderByDesc('last_seen_at')
            ->limit(25)
            ->get()
            ->map(fn ($d) => [
                'node_id'            => $d->node_id,
                'device_label'       => $d->device_label,
                'platform'           => $d->platform,
                'app_version'        => $d->app_version,
                'runtime_version'    => $d->runtime_version,
                'capability_tier'    => $d->capability_tier,
                'trust_level'        => $d->trust_level,
                'is_rate_limited'    => (bool) $d->is_rate_limited,
                'signature_failures' => (int) ($d->signature_failures ?? 0),
                'queue_backlog'      => (int) ($d->queue_backlog ?? 0),
                'last_seen_at'       => $d->last_seen_at?->toIso8601String(),
                'last_sync_at'       => $d->last_sync_at?->toIso8601String(),
                'last_success_at'    => $d->last_success_at?->toIso8601String(),
                'last_failure_at'    => $d->last_failure_at?->toIso8601String(),
                'signal_count'       => $d->signal_count ?? 0,
                'pending_count'      => $d->pending_count ?? 0,
                'failed_count'       => $d->failed_count ?? 0,
                'capability_profile' => $d->capability_profile,
            ]);

        $stuckOfflineThreshold = now()->subHours(24);
        $stuckNodes = TzPwaDevice::where('company_id', $companyId)
            ->where(fn ($q) => $q->where('last_seen_at', '<', $stuckOfflineThreshold)->orWhereNull('last_seen_at'))
            ->count();

        $suspiciousNodes = TzPwaDevice::where('company_id', $companyId)
            ->where('signature_failures', '>=', 3)
            ->count();

        return [
            'summary' => [
                'total_devices'        => $totalDevices,
                'rate_limited_devices' => $rateLimitedDevices,
                'stuck_offline_nodes'  => $stuckNodes,
                'suspicious_nodes'     => $suspiciousNodes,
                'unresolved_conflicts' => $conflictCount,
                'dead_letter_count'    => $deadLetterCount,
                'trust_breakdown'      => $trustBreakdown,
                'tier_breakdown'       => $tierBreakdown,
                'total_ingress'        => $totalIngress,
                'pending_ingress'      => $pendingIngress,
                'promoted_ingress'     => $promotedIngress,
                'failed_ingress'       => $failedIngress,
                'deferred_ingress'     => $deferredIngress,
                'last_promotion_at'    => $lastPromotionAt,
            ],
            'devices' => $recentDevices,
        ];
    }
}
