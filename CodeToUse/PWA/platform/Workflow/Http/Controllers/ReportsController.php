<?php

namespace Modules\Workflow\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Basic aggregates
        $stats = [
            'workflows_total' => DB::table('workflows')->count(),
            'steps_total' => DB::table('workflow_steps')->count(),
            'steps_done' => DB::table('workflow_steps')->where('status','done')->count(),
            'steps_failed' => DB::table('workflow_steps')->where('status','failed')->count(),
            'logs_total' => DB::table('workflow_logs')->count(),
        ];

        // Failures by handler
        $failures = DB::table('workflow_steps')
            ->select('handler', DB::raw('COUNT(*) as cnt'))
            ->where('status','failed')
            ->groupBy('handler')
            ->orderByDesc('cnt')
            ->limit(20)
            ->get();

        // Avg completion time (rough: completed_at - started_at)
        $avg = DB::table('workflow_steps')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_seconds'))
            ->value('avg_seconds');

        return view('workflow::reports.index', [
            'stats' => $stats,
            'failures' => $failures,
            'avg_seconds' => $avg,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $rows = DB::table('workflow_logs')
            ->select('workflow_id','step_id','level','message','context','created_at')
            ->orderBy('id','desc')
            ->limit(5000)
            ->get();

        $fn = 'workflow_logs_export_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fn.'"',
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['workflow_id','step_id','level','message','context','created_at']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->workflow_id, $r->step_id, $r->level, $r->message,
                    is_string($r->context) ? $r->context : json_encode($r->context),
                    $r->created_at
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
