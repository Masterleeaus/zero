<?php

namespace App\Extensions\TitanRewind\System\Http\Controllers;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use App\Extensions\TitanRewind\System\Services\RewindEngine;
use App\Extensions\TitanRewind\System\Services\RewindHistoryService;
use App\Extensions\TitanRewind\System\Services\RewindReplayService;
use App\Extensions\TitanRewind\System\Services\RewindSignalIntegrationService;
use App\Extensions\TitanRewind\System\Services\RewindSnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class TitanRewindApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $this->assertAccess($user, config('titan-rewind.permissions.api'));
        $companyId = $user->company_id ?? $user->id;

        $cases = RewindCase::query()
            ->where('company_id', $companyId)
            ->latest()
            ->limit((int) $request->integer('limit', 20))
            ->get(['id', 'title', 'status', 'severity', 'process_id', 'entity_type', 'entity_id', 'detected_at', 'rollback_completed_at']);

        return response()->json(['data' => $cases]);
    }

    public function history(RewindCase $case, RewindHistoryService $history): JsonResponse
    {
        $this->assertTenant($case);
        $this->assertAccess(Auth::user(), config('titan-rewind.permissions.api'));
        return response()->json($history->history($case));
    }

    public function replay(RewindCase $case, RewindReplayService $replay): JsonResponse
    {
        $this->assertTenant($case);
        $this->assertAccess(Auth::user(), config('titan-rewind.permissions.api'));
        return response()->json($replay->replayBundle($case));
    }

    public function snapshots(RewindCase $case, RewindSnapshotService $snapshots): JsonResponse
    {
        $this->assertTenant($case);
        $this->assertAccess(Auth::user(), config('titan-rewind.permissions.api'));
        return response()->json(['data' => $snapshots->snapshotsForCase($case)]);
    }

    public function candidates(Request $request, RewindSignalIntegrationService $integration): JsonResponse
    {
        $user = Auth::user();
        $this->assertAccess($user, config('titan-rewind.permissions.api'));
        $companyId = $user->company_id ?? $user->id;
        return response()->json(['data' => $integration->autoInitiateCandidates($companyId, (int) $request->integer('limit', 25))]);
    }

    public function promoteLifecycle(Request $request, RewindCase $case, RewindSignalIntegrationService $integration): JsonResponse
    {
        $this->assertTenant($case);
        $user = Auth::user();
        $this->assertAccess($user, config('titan-rewind.permissions.manage'));
        $data = $request->validate(['state' => 'required|string|max:60']);
        $integration->promoteCaseLifecycle($case, $data['state'], ['type' => 'user', 'id' => $user->id], ['api' => true]);
        return response()->json(['status' => 'ok', 'state' => $data['state']]);
    }

    public function initiate(Request $request, RewindEngine $engine): JsonResponse
    {
        $user = Auth::user();
        $this->assertAccess($user, config('titan-rewind.permissions.manage'));
        $data = $request->validate([
            'process_id' => 'nullable|string|max:80',
            'entity_type' => 'required|string|max:80',
            'entity_id' => 'nullable|string|max:80',
            'reason' => 'required|string|max:500',
            'severity' => 'nullable|string|max:30',
            'team_id' => 'nullable|integer',
        ]);

        $case = $engine->initiate([
            'company_id' => $user->company_id ?? $user->id,
            'team_id' => $data['team_id'] ?? null,
            'user_id' => $user->id,
            'actor_id' => $user->id,
            'actor_type' => 'user',
            'process_id' => $data['process_id'] ?? null,
            'entity_type' => $data['entity_type'],
            'entity_id' => $data['entity_id'] ?? null,
            'reason' => $data['reason'],
            'severity' => $data['severity'] ?? 'high',
            'source_type' => 'api',
        ]);

        return response()->json(['status' => 'ok', 'case_id' => $case->id], 201);
    }

    private function assertTenant(RewindCase $case): void
    {
        $user = Auth::user();
        abort_if((int) $case->company_id !== (int) ($user->company_id ?? $user->id), 403);
    }

    private function assertAccess($user, ?string $permission): void
    {
        if (!$permission) {
            return;
        }
        $can = method_exists($user, 'can') ? $user->can($permission) : true;
        $isAdmin = (bool) data_get($user, 'is_admin', false);
        if (!$can && !$isAdmin) {
            abort(403);
        }
    }
}
