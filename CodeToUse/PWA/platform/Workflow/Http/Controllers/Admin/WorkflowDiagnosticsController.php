<?php

namespace Modules\Workflow\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WorkflowDiagnosticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'superadmin']);
        $this->middleware('permission:manage_workflow');
    }

    public function index()
    {
        $tables = [
            'workflows',
            'workflow_steps',
            'workflow_runs',
            'workflow_run_steps',
            'workflow_logs',
        ];

        $tableStatus = [];
        foreach ($tables as $t) {
            try {
                $tableStatus[$t] = DB::getSchemaBuilder()->hasTable($t);
            } catch (\Throwable $e) {
                $tableStatus[$t] = false;
            }
        }

        $endpoints = config('workflow.endpoints', []);

        return view('workflow::admin.diagnostics.index', [
            'tableStatus' => $tableStatus,
            'endpoints' => $endpoints,
        ]);
    }
}
