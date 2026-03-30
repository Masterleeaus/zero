<?php

namespace Modules\Workflow\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Workflow\Entities\Workflow;
use Modules\Workflow\Entities\WorkflowLog;
use Modules\Workflow\Services\TriggerRegistry;
use Modules\Workflow\Services\ActionRegistry;
use Modules\Workflow\Services\WorkflowEngine;

class WorkflowsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view_workflow')->only(['index','show','timeline']);
        $this->middleware('permission:manage_workflow')->only(['create','store','edit','update','run','destroy']);
    }

    public function index()
    {
        $workflows = Workflow::orderByDesc('id')->paginate(20);
        return view('workflow::account.workflows.index', [
            'workflows' => $workflows,
        ]);
    }

    public function create()
    {
        return view('workflow::account.workflows.form', [
            'workflow' => new Workflow(),
            'triggers' => TriggerRegistry::all(),
            'actions' => ActionRegistry::all(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'trigger_event' => 'nullable|string|max:191',
        ]);

        $data['is_active'] = (bool)($request->input('is_active', 0));

        $wf = Workflow::create($data);

        return redirect()->route('workflow.admin.workflows.edit', $wf->id)->with('status', 'Workflow created');
    }

    public function edit(int $id)
    {
        $workflow = Workflow::findOrFail($id);

        return view('workflow::account.workflows.form', [
            'workflow' => $workflow,
            'triggers' => TriggerRegistry::all(),
            'actions' => ActionRegistry::all(),
            'mode' => 'edit',
        ]);
    }

    public function update(int $id, Request $request)
    {
        $workflow = Workflow::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'trigger_event' => 'nullable|string|max:191',
        ]);

        $data['is_active'] = (bool)($request->input('is_active', 0));

        $workflow->update($data);

        return redirect()->route('workflow.admin.workflows.edit', $workflow->id)->with('status', 'Workflow updated');
    }

    public function timeline(int $id)
    {
        $workflow = Workflow::findOrFail($id);
        $logs = WorkflowLog::where('workflow_id', $id)->orderByDesc('id')->paginate(50);

        return view('workflow::account.workflows.timeline', [
            'workflow' => $workflow,
            'logs' => $logs,
        ]);
    }

    public function run(int $id, WorkflowEngine $engine)
    {
        $workflow = Workflow::findOrFail($id);
        $engine->runWorkflow($workflow->id, [
            'event_name' => 'manual',
        ]);

        return redirect()->route('workflow.admin.workflows.timeline', $workflow->id)->with('status', 'Workflow executed');
    }
}
