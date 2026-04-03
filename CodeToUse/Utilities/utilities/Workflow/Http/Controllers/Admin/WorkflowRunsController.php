<?php

namespace Modules\Workflow\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Workflow\Entities\WorkflowRun;

class WorkflowRunsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'superadmin']);
        $this->middleware('permission:view_workflow');
    }

    public function index()
    {
        $runs = WorkflowRun::orderByDesc('id')->paginate(50);
        return view('workflow::admin.runs.index', compact('runs'));
    }

    public function show(int $runId)
    {
        $run = WorkflowRun::with('steps')->findOrFail($runId);
        return view('workflow::admin.runs.show', compact('run'));
    }
}
