<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\JobType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobTypeController extends CoreController
{
    public function index(Request $request): View
    {
        $query = JobType::query();

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $types = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('default.panel.user.work.job-types.index', [
            'types'  => $types,
            'search' => $search ?? '',
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.work.job-types.form', [
            'type' => new JobType(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $type = JobType::query()->create($data);

        return to_route('dashboard.work.job-types.show', $type)->with([
            'type'    => 'success',
            'message' => __('Job type created.'),
        ]);
    }

    public function show(JobType $jobType): View
    {
        return view('default.panel.user.work.job-types.show', [
            'type' => $jobType->load('serviceJobs', 'templates'),
        ]);
    }

    public function edit(JobType $jobType): View
    {
        return view('default.panel.user.work.job-types.form', [
            'type' => $jobType,
        ]);
    }

    public function update(Request $request, JobType $jobType): RedirectResponse
    {
        $data = $this->validated($request);
        $jobType->update($data);

        return to_route('dashboard.work.job-types.show', $jobType)->with([
            'type'    => 'success',
            'message' => __('Job type updated.'),
        ]);
    }

    public function destroy(JobType $jobType): RedirectResponse
    {
        $jobType->delete();

        return to_route('dashboard.work.job-types.index')->with([
            'type'    => 'success',
            'message' => __('Job type deleted.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
    }
}
