<?php

namespace App\Extensions\TitanRewind\System\Services;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use Illuminate\Support\Facades\DB;

class RewindHistoryService
{
    public function __construct(
        private readonly RewindAuditService $audit,
        private readonly RewindProcessBridgeService $processBridge,
        private readonly RewindRollbackPlannerService $planner,
        private readonly RewindSnapshotService $snapshots,
    ) {
    }

    public function history(RewindCase $case): array
    {
        $timeline = $this->audit->timeline($case->company_id, $case->id);
        $links = DB::table('tz_rewind_links')->where('company_id', $case->company_id)->where('case_id', $case->id)->orderBy('depth')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        $actions = DB::table('titan_rewind_actions')->where('company_id', $case->company_id)->where('case_id', $case->id)->orderByDesc('id')->get()->map(fn ($row) => (array) $row)->all();
        $conflicts = DB::table('tz_rewind_conflicts')->where('company_id', $case->company_id)->where('case_id', $case->id)->orderByDesc('id')->get()->map(fn ($row) => (array) $row)->all();

        $processSnapshot = $this->processBridge->snapshot([
            'company_id' => $case->company_id,
            'process_id' => $case->process_id,
            'entity_type' => $case->entity_type,
            'entity_id' => $case->entity_id,
        ]);
        $rollbackPlan = $this->planner->plan($case);
        $snapshots = $this->snapshots->snapshotsForCase($case);

        return [
            'case' => [
                'id' => $case->id,
                'title' => $case->title,
                'status' => $case->status,
                'severity' => $case->severity,
                'process_id' => $case->process_id,
                'correction_process_id' => $case->correction_process_id,
                'replacement_process_id' => $case->replacement_process_id,
                'entity_type' => $case->entity_type,
                'entity_id' => $case->entity_id,
                'detected_at' => optional($case->detected_at)->toIso8601String(),
                'rollback_completed_at' => optional($case->rollback_completed_at)->toIso8601String(),
            ],
            'timeline' => $timeline,
            'links' => $links,
            'actions' => $actions,
            'conflicts' => $conflicts,
            'process_snapshot' => $processSnapshot,
            'rollback_plan' => $rollbackPlan,
            'snapshots' => $snapshots,
            'counts' => [
                'timeline' => count($timeline),
                'links' => count($links),
                'actions' => count($actions),
                'conflicts' => count($conflicts),
                'states' => count($processSnapshot['states'] ?? []),
                'signals' => count($processSnapshot['signals'] ?? []),
                'plan_stages' => count($rollbackPlan['stages'] ?? []),
                'snapshots' => count($snapshots),
            ],
        ];
    }
}
