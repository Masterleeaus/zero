<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Controller;
use App\Models\Work\FieldServiceProject;
use App\Models\Work\ServiceJob;
use App\Services\Work\FieldServiceProjectService;
use Illuminate\Http\Request;

class FieldServiceProjectController extends Controller
{
    public function __construct(private readonly FieldServiceProjectService $projectService)
    {
    }

    public function index(Request $request)
    {
        $projects = FieldServiceProject::query()
            ->with(['customer', 'premises', 'team'])
            ->latest()
            ->paginate(20);

        return view('default.panel.work.projects.index', compact('projects'));
    }

    public function show(int $id)
    {
        $project = FieldServiceProject::with([
            'customer', 'premises', 'team', 'assignedUser',
            'serviceJobs.stage', 'serviceVisits',
        ])->findOrFail($id);

        return view('default.panel.work.projects.show', compact('project'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'reference'        => 'nullable|string|max:100',
            'description'      => 'nullable|string',
            'status'           => 'nullable|in:active,on_hold,completed,cancelled',
            'customer_id'      => 'nullable|exists:customers,id',
            'premises_id'      => 'nullable|exists:premises,id',
            'team_id'          => 'nullable|exists:teams,id',
            'assigned_user_id' => 'nullable|exists:users,id',
            'planned_start'    => 'nullable|date',
            'planned_end'      => 'nullable|date|after_or_equal:planned_start',
            'estimated_hours'  => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        $project = $this->projectService->createProject($data);

        return redirect()->route('work.projects.show', $project->id)
            ->with('success', 'Project created.');
    }

    public function update(Request $request, int $id)
    {
        $project = FieldServiceProject::findOrFail($id);

        $data = $request->validate([
            'name'             => 'sometimes|required|string|max:255',
            'reference'        => 'nullable|string|max:100',
            'description'      => 'nullable|string',
            'status'           => 'nullable|in:active,on_hold,completed,cancelled',
            'customer_id'      => 'nullable|exists:customers,id',
            'premises_id'      => 'nullable|exists:premises,id',
            'team_id'          => 'nullable|exists:teams,id',
            'assigned_user_id' => 'nullable|exists:users,id',
            'planned_start'    => 'nullable|date',
            'planned_end'      => 'nullable|date',
            'estimated_hours'  => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        $project = $this->projectService->updateProject($project, $data);

        return redirect()->route('work.projects.show', $project->id)
            ->with('success', 'Project updated.');
    }

    public function linkJob(Request $request, int $id)
    {
        $project = FieldServiceProject::findOrFail($id);
        $data    = $request->validate(['job_id' => 'required|exists:service_jobs,id']);

        $job = ServiceJob::findOrFail($data['job_id']);
        $this->projectService->linkJob($project, $job);

        return back()->with('success', 'Job linked to project.');
    }
}
