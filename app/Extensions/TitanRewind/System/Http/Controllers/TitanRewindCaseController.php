<?php

namespace App\Extensions\TitanRewind\System\Http\Controllers;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use App\Extensions\TitanRewind\System\Models\RewindConflict;
use App\Extensions\TitanRewind\System\Models\RewindFix;
use App\Extensions\TitanRewind\System\Services\RewindAuditService;
use App\Extensions\TitanRewind\System\Services\RewindCaseService;
use App\Extensions\TitanRewind\System\Services\RewindEngine;
use App\Extensions\TitanRewind\System\Services\RewindFixService;
use App\Extensions\TitanRewind\System\Services\RewindHistoryService;
use App\Extensions\TitanRewind\System\Services\RewindReplayService;
use App\Extensions\TitanRewind\System\Services\RewindRollbackPlannerService;
use App\Extensions\TitanRewind\System\Services\RewindSignalIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class TitanRewindCaseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $this->assertAccess($user, config('titan-rewind.permissions.view'));
        $companyId = $user->company_id ?? $user->id;
        $cases = RewindCase::query()->where('company_id', $companyId)->latest()->paginate(20);
        $openConflicts = RewindConflict::query()->where('company_id', $companyId)->where('status', 'open')->count();
        $conflictHoldCases = RewindCase::query()->where('company_id', $companyId)->where('status', 'conflict-hold')->count();
        return view('titan-rewind::cases.index', compact('cases', 'openConflicts', 'conflictHoldCases'));
    }

    public function manualReview(Request $request)
    {
        $user = Auth::user();
        $this->assertAccess($user, config('titan-rewind.permissions.resolve'));
        $companyId = $user->company_id ?? $user->id;
        $cases = RewindCase::query()->where('company_id', $companyId)->whereIn('status', ['conflict-hold', 'awaiting-correction'])->latest()->paginate(20);
        return view('titan-rewind::cases.index', ['cases' => $cases, 'openConflicts' => RewindConflict::query()->where('company_id', $companyId)->where('status', 'open')->count(), 'conflictHoldCases' => $cases->total()]);
    }

    public function show(RewindCase $case, RewindAuditService $audit, RewindHistoryService $history)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.view'));
        $case->load(['fixes', 'links', 'conflicts', 'snapshots']);
        $timeline = $audit->timeline($case->company_id, $case->id);
        $historyBundle = $history->history($case);
        return view('titan-rewind::cases.show', compact('case', 'timeline', 'historyBundle'));
    }

    public function timeline(RewindCase $case, RewindHistoryService $history)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.view'));
        return response()->json($history->history($case));
    }

    public function plan(RewindCase $case, RewindRollbackPlannerService $planner)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.view'));
        return response()->json($planner->plan($case));
    }

    public function replay(RewindCase $case, RewindReplayService $replay)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.view'));
        return response()->json($replay->replayBundle($case));
    }

    public function promoteLifecycle(RewindCase $case, Request $request, RewindSignalIntegrationService $integration)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.manage'));
        $data = $request->validate(['state' => 'required|string|max:60']);
        $integration->promoteCaseLifecycle($case, $data['state'], ['type' => 'user', 'id' => $user->id], ['manual' => true]);
        return redirect()->route('titanrewind.cases.show', ['case' => $case->id])->with('success', 'Lifecycle promoted.');
    }

    public function initiate(Request $request, RewindEngine $engine)
    {
        $user = Auth::user();
        $this->assertAccess($user, config('titan-rewind.permissions.manage'));
        $data = $request->validate([
            'process_id' => 'nullable|string|max:80','entity_type' => 'required|string|max:80','entity_id' => 'nullable|string|max:80',
            'reason' => 'required|string|max:500','severity' => 'nullable|string|max:30','team_id' => 'nullable|integer',
        ]);
        $case = $engine->initiate([
            'company_id' => $user->company_id ?? $user->id,'team_id' => $data['team_id'] ?? null,'user_id' => $user->id,'actor_id' => $user->id,'actor_type' => 'user',
            'process_id' => $data['process_id'] ?? null,'entity_type' => $data['entity_type'],'entity_id' => $data['entity_id'] ?? null,'reason' => $data['reason'],
            'severity' => $data['severity'] ?? 'high','source_type' => 'manual',
        ]);
        return redirect()->route('titanrewind.cases.show', ['case' => $case->id])->with('success', 'Rewind initiated.');
    }

    public function submitCorrection(Request $request, RewindCase $case, RewindEngine $engine)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.manage'));
        $data = $request->validate([
            'process_id' => 'nullable|string|max:80',
            'correction_json' => 'required|string',
            'complete_now' => 'nullable|boolean',
            'correction_entity_id' => 'nullable|string|max:80',
        ]);
        $correctionJson = json_decode($data['correction_json'], true);
        if (!is_array($correctionJson)) {
            return back()->withErrors(['correction_json' => 'Correction JSON must decode to an object.']);
        }
        $result = $engine->submitCorrection($case, array_merge($correctionJson, [
            'process_id' => $data['process_id'] ?? null,
            'company_id' => $case->company_id,
        ]), ['type' => 'user', 'id' => $user->id]);

        if ((bool) ($data['complete_now'] ?? false)) {
            $engine->completeRollback($case->fresh(), ['type' => 'user', 'id' => $user->id], [
                'correction_process_id' => $result['correction_process_id'],
                'correction_entity_id' => $data['correction_entity_id'] ?? null,
            ]);
        }

        return redirect()->route('titanrewind.cases.show', ['case' => $case->id])->with('success', 'Correction submitted.');
    }

    public function completeRollback(Request $request, RewindCase $case, RewindEngine $engine)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.manage'));
        $data = $request->validate([
            'correction_process_id' => 'nullable|string|max:80',
            'correction_entity_id' => 'nullable|string|max:80',
        ]);
        $engine->completeRollback($case, ['type' => 'user', 'id' => $user->id], $data);
        return redirect()->route('titanrewind.cases.show', ['case' => $case->id])->with('success', 'Rollback completed.');
    }

    public function resolveConflict(Request $request, RewindCase $case, RewindConflict $conflict, RewindEngine $engine)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.resolve'));
        abort_if((int) $conflict->case_id !== (int) $case->id, 404);
        $data = $request->validate([
            'resolution' => 'required|string|max:30',
            'notes' => 'nullable|string|max:500',
        ]);
        $engine->resolveConflict($case, $conflict, ['type' => 'user', 'id' => $user->id], $data['resolution'], ['notes' => $data['notes'] ?? null]);
        return redirect()->route('titanrewind.cases.show', ['case' => $case->id])->with('success', 'Conflict updated.');
    }

    public function proposeFix(Request $request, RewindCase $case, RewindFixService $fixService, RewindAuditService $audit)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.manage'));
        $data = $request->validate(['fix_type' => 'required|string|max:80','proposal_json' => 'nullable|string','requires_confirmation' => 'nullable|boolean']);
        $proposalJson = !empty($data['proposal_json']) ? (json_decode($data['proposal_json'], true) ?: []) : [];
        $proposalJson['fix_type'] = $data['fix_type'];
        $fix = $fixService->proposeFix($case, $proposalJson, ['type' => 'user', 'id' => $user->id], (bool) ($data['requires_confirmation'] ?? true));
        $audit->appendEvent([
            'company_id' => $case->company_id,'team_id' => $case->team_id,'user_id' => $case->user_id,'case_id' => $case->id,
            'event_type' => 'fix_proposed','entity_type' => 'titan_rewind_fixes','entity_id' => $fix->id,'actor_type' => 'user','actor_id' => $user->id,
            'payload_json' => ['fix_type' => $fix->fix_type],'idempotency_key' => 'fix_proposed:' . $fix->id,
        ]);
        return redirect()->route('titanrewind.cases.show', ['case' => $case->id])->with('success', 'Fix proposed.');
    }

    public function applyFix(Request $request, RewindCase $case, RewindFixService $fixService, RewindAuditService $audit)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.manage'));
        $data = $request->validate(['fix_id' => 'required|integer','confirm' => 'nullable|boolean']);
        $fix = RewindFix::query()->where('id', $data['fix_id'])->where('case_id', $case->id)->where('company_id', $case->company_id)->firstOrFail();
        if (($data['confirm'] ?? false) && $fix->status === 'proposed') {
            $fixService->confirmFix($fix, ['type' => 'user', 'id' => $user->id]);
            $audit->appendEvent([
                'company_id' => $case->company_id,'team_id' => $case->team_id,'user_id' => $case->user_id,'case_id' => $case->id,
                'event_type' => 'fix_confirmed','entity_type' => 'titan_rewind_fixes','entity_id' => $fix->id,'actor_type' => 'user','actor_id' => $user->id,
                'payload_json' => ['fix_type' => $fix->fix_type],'idempotency_key' => 'fix_confirmed:' . $fix->id,
            ]);
        }
        $fixService->applyFix($fix, ['type' => 'user', 'id' => $user->id]);
        $audit->appendEvent([
            'company_id' => $case->company_id,'team_id' => $case->team_id,'user_id' => $case->user_id,'case_id' => $case->id,
            'event_type' => 'fix_applied','entity_type' => 'titan_rewind_fixes','entity_id' => $fix->id,'actor_type' => 'user','actor_id' => $user->id,
            'payload_json' => ['status' => $fix->status],'idempotency_key' => 'fix_applied:' . $fix->id,
        ]);
        return redirect()->route('titanrewind.cases.show', ['case' => $case->id])->with('success', 'Fix processed.');
    }

    public function resolve(Request $request, RewindCase $case, RewindCaseService $caseService, RewindAuditService $audit)
    {
        $user = Auth::user();
        $this->assertTenant($case, $user);
        $this->assertAccess($user, config('titan-rewind.permissions.resolve'));
        $caseService->resolveCase($case, ['type' => 'user', 'id' => $user->id]);
        $audit->appendEvent([
            'company_id' => $case->company_id,'team_id' => $case->team_id,'user_id' => $case->user_id,'case_id' => $case->id,
            'event_type' => 'case_resolved','actor_type' => 'user','actor_id' => $user->id,'payload_json' => [],'idempotency_key' => 'case_resolved:' . $case->id,
        ]);
        return redirect()->route('titanrewind.cases.show', ['case' => $case->id])->with('success', 'Case resolved.');
    }

    private function assertTenant(RewindCase $case, $user): void
    {
        if ((int) $case->company_id !== (int) ($user->company_id ?? $user->id)) abort(403);
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
