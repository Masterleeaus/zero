<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\JobTemplate;
use App\Models\Work\JobType;
use App\Models\Team\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobTemplateController extends CoreController
{
    public function index(Request $request): View
    {
        $query = JobTemplate::query()->with(['jobType', 'team']);

        if ($typeId = $request->integer('job_type_id')) {
            $query->where('job_type_id', $typeId);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $templates = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('default.panel.user.work.job-templates.index', [
            'templates' => $templates,
            'jobTypes'  => $this->jobTypes(),
            'filters'   => [
                'job_type_id' => $typeId ?? '',
                'search'      => $search ?? '',
            ],
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.work.job-templates.form', [
            'template' => new JobTemplate(),
            'jobTypes' => $this->jobTypes(),
            'teams'    => $this->teams(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $template = JobTemplate::query()->create($data);

        return to_route('dashboard.work.job-templates.show', $template)->with([
            'type'    => 'success',
            'message' => __('Job template created.'),
        ]);
    }

    public function show(JobTemplate $jobTemplate): View
    {
        return view('default.panel.user.work.job-templates.show', [
            'template' => $jobTemplate->load('jobType', 'team'),
        ]);
    }

    public function edit(JobTemplate $jobTemplate): View
    {
        return view('default.panel.user.work.job-templates.form', [
            'template' => $jobTemplate,
            'jobTypes' => $this->jobTypes(),
            'teams'    => $this->teams(),
        ]);
    }

    public function update(Request $request, JobTemplate $jobTemplate): RedirectResponse
    {
        $data = $this->validated($request);
        $jobTemplate->update($data);

        return to_route('dashboard.work.job-templates.show', $jobTemplate)->with([
            'type'    => 'success',
            'message' => __('Job template updated.'),
        ]);
    }

    public function destroy(JobTemplate $jobTemplate): RedirectResponse
    {
        $jobTemplate->delete();

        return to_route('dashboard.work.job-templates.index')->with([
            'type'    => 'success',
            'message' => __('Job template deleted.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'job_type_id'  => [
                'nullable',
                'integer',
                Rule::exists('job_types', 'id')->where(static function ($query) {
                    return $query->when(auth()->check(), function ($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                }),
            ],
            'team_id'      => ['nullable', 'integer', Rule::exists('teams', 'id')],
            'instructions' => ['nullable', 'string'],
            'duration'     => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function jobTypes()
    {
        return JobType::query()
            ->when(auth()->check(), fn ($q) => $q->where('company_id', auth()->user()->company_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function teams()
    {
        return Team::query()
            ->when(auth()->check(), fn ($q) => $q->where('company_id', auth()->user()->company_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
