<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\ServiceJob;
use App\Models\Work\Timelog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TimelogController extends CoreController
{
    public function index(Request $request): View
    {
        $timelogs = Timelog::query()
            ->where('company_id', $request->user()?->company_id)
            ->with(['serviceJob'])
            ->latest('started_at')
            ->paginate(15);

        return view('default.panel.user.work.timelogs.index', compact('timelogs'));
    }

    public function create(): View
    {
        $jobs = ServiceJob::query()
            ->forCompany(auth()->user()?->company_id)
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('default.panel.user.work.timelogs.create', compact('jobs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_job_id' => ['nullable', 'exists:service_jobs,id'],
            'started_at'     => ['required', 'date'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['service_job_id'] ?? null) {
            $jobCompany = ServiceJob::query()
                ->whereKey($validated['service_job_id'])
                ->value('company_id');

            abort_if($jobCompany !== $request->user()?->company_id, 403);
        }

        Timelog::query()->create([
            'company_id'     => $request->user()?->company_id,
            'user_id'        => $request->user()?->id,
            'service_job_id' => $validated['service_job_id'] ?? null,
            'started_at'     => $validated['started_at'],
            'notes'          => $validated['notes'] ?? null,
        ]);

        return redirect()->route('dashboard.work.timelogs.index')
            ->with('message', __('Timelog started'));
    }

    public function stop(Request $request, Timelog $timelog): RedirectResponse
    {
        abort_if($timelog->company_id !== $request->user()?->company_id, 403);

        $timelog->update([
            'ended_at' => now(),
        ]);

        return back()->with('message', __('Timelog stopped'));
    }
}
