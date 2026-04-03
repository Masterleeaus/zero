<?php

namespace App\Http\Controllers\TitanPwa;

use App\Http\Controllers\Controller;
use App\Services\TitanZeroPwaSystem\TitanPwaManifestService;
use App\Services\TitanZeroPwaSystem\TitanPwaSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TitanPwaController extends Controller
{
    public function __construct(
        protected TitanPwaManifestService $manifestService,
        protected TitanPwaSyncService $syncService,
    ) {}

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
     * Return client bootstrap configuration.
     */
    public function bootstrap(): JsonResponse
    {
        return response()->json($this->manifestService->bootstrapConfig());
    }

    /**
     * Device handshake — register or refresh a PWA node.
     */
    public function handshake(Request $request): JsonResponse
    {
        $this->middleware('auth');

        $validated = $request->validate([
            'node_id'  => 'required|string|max:255',
            'platform' => 'required|string|max:100',
            'meta'     => 'sometimes|array',
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
        $this->middleware('auth');

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

        $accepted = collect($results)->where('status', 'accepted')->count();
        $rejected = collect($results)->where('status', 'rejected')->count();

        return response()->json([
            'ok'       => true,
            'accepted' => $accepted,
            'rejected' => $rejected,
            'results'  => $results,
        ]);
    }

    /**
     * Return sync status for the current device.
     */
    public function syncStatus(Request $request): JsonResponse
    {
        $this->middleware('auth');

        $nodeId = $request->query('node_id') ?? $request->input('node_id');

        if (! $nodeId) {
            return response()->json(['error' => 'node_id is required'], 422);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        $status = $this->syncService->status((string) $nodeId, (int) $user->company_id);

        return response()->json($status);
    }
}
