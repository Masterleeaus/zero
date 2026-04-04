<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Sync;

use App\Http\Controllers\Controller;
use App\Models\Sync\EdgeDeviceSession;
use App\Models\Sync\EdgeSyncConflict;
use App\Services\Sync\EdgeSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * MODULE 05 — TitanEdgeSync API
 *
 * All routes are protected by Passport token authentication (auth:api).
 */
class EdgeSyncController extends Controller
{
    public function __construct(
        private readonly EdgeSyncService $syncService,
    ) {}

    /**
     * POST /api/sync/register
     *
     * Register or refresh a device session.
     * Required: device_id (string), optional: device_name, platform.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id'   => ['required', 'string', 'max:100'],
            'device_name' => ['nullable', 'string', 'max:200'],
            'platform'    => ['nullable', 'string', 'in:ios,android,web,pwa'],
        ]);

        $session = $this->syncService->registerDevice(Auth::user(), $data);

        return response()->json([
            'status'      => 'registered',
            'device_id'   => $session->device_id,
            'sync_cursor' => $session->sync_cursor,
            'platform'    => $session->platform,
        ]);
    }

    /**
     * POST /api/sync/push
     *
     * Accept a batch of offline operations from a device.
     * Required: device_id, operations (array of operation objects).
     */
    public function push(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id'                           => ['required', 'string', 'max:100'],
            'operations'                          => ['required', 'array', 'min:1'],
            'operations.*.operation_type'         => ['required', 'string', 'in:job_update,checklist_response,inspection_response,evidence_upload,signature_capture,job_complete'],
            'operations.*.subject_type'           => ['nullable', 'string', 'max:150'],
            'operations.*.subject_id'             => ['nullable', 'integer'],
            'operations.*.payload'                => ['required', 'array'],
            'operations.*.client_created_at'      => ['nullable', 'date'],
        ]);

        $result = $this->syncService->ingestBatch(
            Auth::user(),
            $data['device_id'],
            $data['operations']
        );

        return response()->json($result, 202);
    }

    /**
     * GET /api/sync/pull
     *
     * Fetch server-side changes since the device's last sync cursor.
     * Required: device_id (query param).
     */
    public function pull(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => ['required', 'string', 'max:100'],
        ]);

        $session = EdgeDeviceSession::withoutGlobalScopes()
            ->where('user_id', Auth::id())
            ->where('device_id', $data['device_id'])
            ->firstOrFail();

        $delta = $this->syncService->getDeltaForDevice($session);

        return response()->json($delta);
    }

    /**
     * POST /api/sync/acknowledge
     *
     * Acknowledge a sync cursor (device confirms it has processed up to this point).
     * Required: device_id, cursor (int).
     */
    public function acknowledge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => ['required', 'string', 'max:100'],
            'cursor'    => ['required', 'integer', 'min:0'],
        ]);

        $session = EdgeDeviceSession::withoutGlobalScopes()
            ->where('user_id', Auth::id())
            ->where('device_id', $data['device_id'])
            ->firstOrFail();

        $session->advanceCursor((int) $data['cursor']);

        return response()->json(['status' => 'acknowledged', 'cursor' => $session->sync_cursor]);
    }

    /**
     * GET /api/sync/conflicts
     *
     * List unresolved conflicts for the current user's devices.
     */
    public function conflicts(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $conflicts = EdgeSyncConflict::query()
            ->join('edge_sync_queues', 'edge_sync_conflicts.sync_queue_id', '=', 'edge_sync_queues.id')
            ->where('edge_sync_queues.user_id', $userId)
            ->whereNull('edge_sync_conflicts.resolved_at')
            ->select('edge_sync_conflicts.*')
            ->with('syncQueue')
            ->orderByDesc('edge_sync_conflicts.created_at')
            ->paginate(50);

        return response()->json($conflicts);
    }

    /**
     * POST /api/sync/conflicts/{id}/resolve
     *
     * Resolve a specific conflict using the given strategy.
     * Required: strategy (server_wins|client_wins|merge|manual).
     */
    public function resolveConflict(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'strategy' => ['required', 'string', 'in:server_wins,client_wins,merge,manual'],
        ]);

        $conflict = EdgeSyncConflict::query()
            ->join('edge_sync_queues', 'edge_sync_conflicts.sync_queue_id', '=', 'edge_sync_queues.id')
            ->where('edge_sync_queues.user_id', Auth::id())
            ->where('edge_sync_conflicts.id', $id)
            ->select('edge_sync_conflicts.*')
            ->firstOrFail();

        // Reload as a proper model instance (not joined select).
        $conflict = EdgeSyncConflict::findOrFail($conflict->id);

        $this->syncService->resolveConflict($conflict, $data['strategy']);

        return response()->json(['status' => 'resolved', 'strategy' => $data['strategy']]);
    }
}
