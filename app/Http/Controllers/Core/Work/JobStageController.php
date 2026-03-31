<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\JobStage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobStageController extends CoreController
{
    private array $stageTypes = ['order', 'location', 'worker', 'equipment'];

    public function index(Request $request): View
    {
        $query = JobStage::query();

        if ($type = $request->string('stage_type')->toString()) {
            $query->where('stage_type', $type);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $stages = $query->orderBy('sequence')->paginate(25)->withQueryString();

        return view('default.panel.user.work.job-stages.index', [
            'stages'     => $stages,
            'stageTypes' => $this->stageTypes,
            'filters'    => [
                'stage_type' => $type ?? '',
                'search'     => $search ?? '',
            ],
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.work.job-stages.form', [
            'stage'      => new JobStage(),
            'stageTypes' => $this->stageTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $stage = JobStage::query()->create($data);

        return to_route('dashboard.work.job-stages.show', $stage)->with([
            'type'    => 'success',
            'message' => __('Job stage created.'),
        ]);
    }

    public function show(JobStage $jobStage): View
    {
        return view('default.panel.user.work.job-stages.show', [
            'stage' => $jobStage->load('serviceJobs'),
        ]);
    }

    public function edit(JobStage $jobStage): View
    {
        return view('default.panel.user.work.job-stages.form', [
            'stage'      => $jobStage,
            'stageTypes' => $this->stageTypes,
        ]);
    }

    public function update(Request $request, JobStage $jobStage): RedirectResponse
    {
        $data = $this->validated($request);
        $jobStage->update($data);

        return to_route('dashboard.work.job-stages.show', $jobStage)->with([
            'type'    => 'success',
            'message' => __('Job stage updated.'),
        ]);
    }

    public function destroy(JobStage $jobStage): RedirectResponse
    {
        $jobStage->delete();

        return to_route('dashboard.work.job-stages.index')->with([
            'type'    => 'success',
            'message' => __('Job stage deleted.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'stage_type'        => ['required', Rule::in($this->stageTypes)],
            'sequence'          => ['nullable', 'integer', 'min:0'],
            'is_closed'         => ['nullable', 'boolean'],
            'is_default'        => ['nullable', 'boolean'],
            'fold'              => ['nullable', 'boolean'],
            'require_signature' => ['nullable', 'boolean'],
            'color'             => ['nullable', 'string', 'max:7'],
            'description'       => ['nullable', 'string'],
        ]);
    }
}
