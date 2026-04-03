<?php

namespace App\Http\Controllers\TitanPwa;

use App\Http\Controllers\Controller;
use App\Models\TzPwaDevice;
use App\Models\TzPwaSignalIngress;
use App\Services\TitanZeroPwaSystem\NodeTrustService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PwaDiagnosticsController extends Controller
{
    public function __construct(
        protected NodeTrustService $trustService,
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

        return view('default.panel.admin.pwa.diagnostics', compact('stats'));
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

        $totalIngress   = TzPwaSignalIngress::where('company_id', $companyId)->count();
        $pendingIngress = TzPwaSignalIngress::where('company_id', $companyId)->where('signal_stage', 'pending')->count();
        $promotedIngress = TzPwaSignalIngress::where('company_id', $companyId)->where('signal_stage', 'promoted')->count();
        $failedIngress  = TzPwaSignalIngress::where('company_id', $companyId)->where('signal_stage', 'failed')->count();

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
            ->limit(20)
            ->get()
            ->map(fn ($d) => [
                'node_id'            => $d->node_id,
                'device_label'       => $d->device_label,
                'platform'           => $d->platform,
                'app_version'        => $d->app_version,
                'trust_level'        => $d->trust_level,
                'is_rate_limited'    => (bool) $d->is_rate_limited,
                'signature_failures' => (int) ($d->signature_failures ?? 0),
                'last_seen_at'       => $d->last_seen_at?->toIso8601String(),
                'last_failure_at'    => $d->last_failure_at?->toIso8601String(),
                'signal_count'       => $d->signal_count ?? 0,
                'pending_count'      => $d->pending_count ?? 0,
                'failed_count'       => $d->failed_count ?? 0,
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
                'trust_breakdown'      => $trustBreakdown,
                'total_ingress'        => $totalIngress,
                'pending_ingress'      => $pendingIngress,
                'promoted_ingress'     => $promotedIngress,
                'failed_ingress'       => $failedIngress,
                'last_promotion_at'    => $lastPromotionAt,
            ],
            'devices' => $recentDevices,
        ];
    }
}
