<?php

namespace App\Http\Controllers\TitanPwa;

use App\Http\Controllers\Controller;
use App\Services\TitanZeroPwaSystem\TitanPwaManifestService;
use App\Services\TitanZeroPwaSystem\TitanPwaSyncService;
use App\Services\TitanZeroPwaSystem\PwaDeferredReplayService;
use App\Services\TitanZeroPwaSystem\PwaStagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TitanPwaController extends Controller
{
    public function __construct(
        protected TitanPwaManifestService $manifestService,
        protected TitanPwaSyncService $syncService,
        protected PwaDeferredReplayService $replayService,
        protected PwaStagingService $stagingService,
    ) {
        // manifest and bootstrap are public; all other endpoints require auth
        $this->middleware('auth')->only(['handshake', 'ingest', 'syncStatus', 'reconnect', 'stageArtifacts', 'stagingStatus']);
    }

    /**
     * Serve the PWA web app manifest.
     */
    public function manifest(): JsonResponse
    {
        return response()
            ->json($this->manifestService->manifest())
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * Return client bootstrap / runtime contract configuration.
     */
    public function bootstrap(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        return response()->json(
            $this->manifestService->bootstrapConfig(
                $user?->id,
                $user?->company_id,
            )
        );
    }

    /**
     * Device handshake — register or refresh a PWA node.
     * Now accepts capability_profile and runtime_version from the client.
     */
    public function handshake(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'node_id'             => 'required|string|max:255',
            'platform'            => 'required|string|max:100',
            'meta'                => 'sometimes|array',
            'runtime_version'     => 'sometimes|string|max:20',
            'capability_profile'  => 'sometimes|array',
            'capability_tier'     => 'sometimes|string|max:30',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $result = $this->syncService->handshake(
            $validated,
            (int) $user->company_id,
            (int) $user->id,
        );

        return response()->json($result);
    }

    /**
     * Ingest a batch of signals from a PWA node.
     */
    public function ingest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'signals'  => 'required|array|min:1',
            'node_id'  => 'required|string|max:255',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $results = $this->syncService->ingest(
            $validated['signals'],
            $validated['node_id'],
            (int) $user->company_id,
            (int) $user->id,
        );

        $resultCollection = collect($results);

        return response()->json([
            'ok'           => true,
            'accepted'     => $resultCollection->where('ingest_status', 'accepted')->count(),
            'rejected'     => $resultCollection->where('ingest_status', 'rejected')->count(),
            'duplicate'    => $resultCollection->where('ingest_status', 'duplicate')->count(),
            'invalid_sig'  => $resultCollection->where('ingest_status', 'invalid_sig')->count(),
            'rate_limited' => $resultCollection->where('ingest_status', 'rate_limited')->count(),
            'deferred'     => $resultCollection->where('ingest_status', 'deferred')->count(),
            'results'      => $results,
        ]);
    }

    /**
     * Return sync status for the current device.
     */
    public function syncStatus(Request $request): JsonResponse
    {
        $nodeId = $request->query('node_id') ?? $request->input('node_id');

        if (! $nodeId) {
            return response()->json(['error' => 'node_id is required'], 422);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        $status = $this->syncService->status((string) $nodeId, (int) $user->company_id);

        return response()->json($status);
    }

    /**
     * Reconnect-triggered replay: re-queue failed/deferred signals for this node.
     */
    public function reconnect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'node_id' => 'required|string|max:255',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $result = $this->replayService->replayForNode(
            $validated['node_id'],
            (int) $user->company_id,
        );

        return response()->json([
            'ok'       => true,
            'replayed' => $result['replayed'],
            'skipped'  => $result['skipped'],
        ]);
    }

    /**
     * Stage offline artifacts (photos, notes, proofs) from a device.
     */
    public function stageArtifacts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'node_id'     => 'required|string|max:255',
            'artifacts'   => 'required|array|min:1',
            'artifacts.*.artifact_type' => 'sometimes|string|max:30',
            'artifacts.*.client_ref'    => 'sometimes|string|max:128',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $results = $this->stagingService->stageArtifacts(
            $validated['artifacts'],
            $validated['node_id'],
            (int) $user->company_id,
            (int) $user->id,
        );

        $resultCollection = collect($results);

        return response()->json([
            'ok'        => true,
            'staged'    => $resultCollection->where('status', 'staged')->count(),
            'duplicate' => $resultCollection->where('status', 'duplicate')->count(),
            'error'     => $resultCollection->where('status', 'error')->count(),
            'results'   => $results,
        ]);
    }

    /**
     * Return staging status for a device node.
     */
    public function stagingStatus(Request $request): JsonResponse
    {
        $nodeId = $request->query('node_id') ?? $request->input('node_id');

        if (! $nodeId) {
            return response()->json(['error' => 'node_id is required'], 422);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        $status = $this->stagingService->statusForNode((string) $nodeId, (int) $user->company_id);

        return response()->json($status);
    }
}
