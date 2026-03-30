<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\Attendance;
use App\Models\Work\ServiceJob;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends CoreController
{
    public function index(Request $request): View
    {
        $attendances = Attendance::query()
            ->where('company_id', $request->user()?->company_id)
            ->with('serviceJob')
            ->latest('check_in_at')
            ->paginate(15);

        return view('default.panel.user.work.attendance.index', compact('attendances'));
    }

    public function create(Request $request): View
    {
        $jobs = ServiceJob::query()
            ->where('company_id', $request->user()?->company_id)
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('default.panel.user.work.attendance.create', compact('jobs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_job_id' => ['nullable', 'exists:service_jobs,id'],
            'check_in_at'    => ['required', 'date'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['service_job_id'] ?? null) {
            $jobCompany = ServiceJob::query()
                ->whereKey($validated['service_job_id'])
                ->value('company_id');
            abort_if($jobCompany !== $request->user()?->company_id, 403);
        }

        Attendance::query()->create([
            'company_id'     => $request->user()?->company_id,
            'user_id'        => $request->user()?->id,
            'service_job_id' => $validated['service_job_id'] ?? null,
            'check_in_at'    => $validated['check_in_at'],
            'notes'          => $validated['notes'] ?? null,
            'status'         => 'open',
        ]);

        return redirect()->route('dashboard.work.attendance.index')
            ->with('message', __('Checked in'));
    }

    public function checkout(Request $request, Attendance $attendance): RedirectResponse
    {
        abort_if($attendance->company_id !== $request->user()?->company_id, 403);

        $attendance->update([
            'check_out_at' => now(),
        ]);

        return back()->with('message', __('Checked out'));
    }
}
