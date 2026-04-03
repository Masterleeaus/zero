<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\Checklist;
use App\Models\Work\ServiceJob;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChecklistController extends CoreController
{
    public function index(Request $request): View
    {
        $query = Checklist::query()->with('job.site');

        if ($jobId = $request->integer('job_id')) {
            $query->where('service_job_id', $jobId);
        }

        if ($request->boolean('pending_only')) {
            $query->where('is_completed', false);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $checklists = $query->latest()->paginate(10)->withQueryString();

        return view('default.panel.user.work.checklists.index', [
            'checklists' => $checklists,
            'jobs'       => ServiceJob::query()->orderByDesc('id')->get(['id', 'title']),
            'filters'    => [
                'job_id'       => $jobId ?? '',
                'pending_only' => $request->boolean('pending_only'),
                'search'       => $search ?? '',
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('default.panel.user.work.checklists.form', [
            'checklist' => new Checklist(),
            'jobs'      => ServiceJob::query()->orderByDesc('id')->get(['id', 'title']),
            'jobId'     => $request->integer('job_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $checklist = Checklist::query()->create($data);

        return to_route('dashboard.work.checklists.show', $checklist)->with([
            'type'    => 'success',
            'message' => __('Checklist created.'),
        ]);
    }

    public function show(Checklist $checklist): View
    {
        return view('default.panel.user.work.checklists.show', [
            'checklist' => $checklist->load('job.site'),
        ]);
    }

    public function edit(Checklist $checklist): View
    {
        return view('default.panel.user.work.checklists.form', [
            'checklist' => $checklist,
            'jobs'      => ServiceJob::query()->orderByDesc('id')->get(['id', 'title']),
            'jobId'     => $checklist->service_job_id,
        ]);
    }

    public function update(Request $request, Checklist $checklist): RedirectResponse
    {
        $data = $this->validated($request);

        $checklist->update($data);

        return back()->with([
            'type'    => 'success',
            'message' => __('Checklist updated.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'service_job_id' => [
                'required',
                'integer',
                Rule::exists('service_jobs', 'id')->where(static function ($query) {
                    return $query->when(auth()->check(), function ($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                }),
            ],
            'title'          => ['required', 'string', 'max:255'],
            'is_completed'   => ['nullable', 'boolean'],
            'notes'          => ['nullable', 'string'],
        ]);

        $data['is_completed'] = $request->boolean('is_completed');

        return $data;
    }
}
