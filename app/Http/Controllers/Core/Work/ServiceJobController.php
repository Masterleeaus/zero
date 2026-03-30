<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceJobController extends CoreController
{
    public function index(Request $request): View
    {
        $query = ServiceJob::query()->with('site');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($siteId = $request->integer('site_id')) {
            $query->where('site_id', $siteId);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(static function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        $jobs = $query->latest()->paginate(10)->withQueryString();

        return view('default.panel.work.jobs.index', [
            'jobs'    => $jobs,
            'sites'   => Site::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'status'  => $status ?? '',
                'site_id' => $siteId ?? '',
                'search'  => $search ?? '',
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('default.panel.work.jobs.form', [
            'job'    => new ServiceJob(),
            'sites'  => Site::query()->orderBy('name')->get(['id', 'name']),
            'siteId' => $request->integer('site_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $job = ServiceJob::query()->create($data);

        return to_route('dashboard.work.service-jobs.show', $job)->with([
            'type'    => 'success',
            'message' => __('Service job created.'),
        ]);
    }

    public function show(ServiceJob $job): View
    {
        return view('default.panel.work.jobs.show', [
            'job' => $job->load(['site', 'checklists']),
        ]);
    }

    public function edit(ServiceJob $job): View
    {
        return view('default.panel.work.jobs.form', [
            'job'    => $job,
            'sites'  => Site::query()->orderBy('name')->get(['id', 'name']),
            'siteId' => $job->site_id,
        ]);
    }

    public function update(Request $request, ServiceJob $job): RedirectResponse
    {
        $data = $this->validated($request);

        $job->update($data);

        return to_route('dashboard.work.service-jobs.show', $job)->with([
            'type'    => 'success',
            'message' => __('Service job updated.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'site_id'      => [
                'required',
                'integer',
                Rule::exists('sites', 'id')->where(static function ($query) {
                    return $query->when(auth()->check(), function ($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                }),
            ],
            'title'        => ['required', 'string', 'max:255'],
            'status'       => ['nullable', 'string', 'max:50'],
            'scheduled_at' => ['nullable', 'date'],
            'notes'        => ['nullable', 'string'],
        ]);
    }
}
