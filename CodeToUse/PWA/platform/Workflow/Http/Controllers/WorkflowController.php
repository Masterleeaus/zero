<?php

namespace Modules\Workflow\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Workflow\Entities\WorkflowLog;
use Modules\Workflow\Services\WorkflowEngine;

class WorkflowController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        return view('workflow::index');
    }

    public function timeline($workflowId)
    {
        $logs = WorkflowLog::where('workflow_id', (int) $workflowId)
            ->orderBy('id', 'desc')
            ->paginate(30);

        return view('workflow::timeline', compact('logs', 'workflowId'));
    }

    public function run($workflowId, WorkflowEngine $engine)
    {
        $this->authorize('manage_workflow');

        $engine->runWorkflow((int) $workflowId);

        return redirect()->back()->with('status', 'Workflow executed.');
    }
}
