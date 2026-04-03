<?php

namespace Modules\Workflow\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Modules\Workflow\Entities\WorkflowRun;

class WorkflowRunsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'company']);
        $this->middleware('permission:view_workflow');
    }

    public function index()
    {
        $runs = WorkflowRun::orderByDesc('id')->paginate(30);
        return view('workflow::account.runs.index', compact('runs'));
    }

    public function show(int $runId)
    {
        $run = WorkflowRun::with('steps')->findOrFail($runId);
        return view('workflow::account.runs.show', compact('run'));
    }
}
