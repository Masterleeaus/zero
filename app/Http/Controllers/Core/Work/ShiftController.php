<?php

namespace App\Http\Controllers\Core\Work;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\Shift;
use App\Models\Work\ServiceJob;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends CoreController
{
    public function index(Request $request): View
    {
        $shifts = Shift::query()
            ->where('company_id', $request->user()->company_id)
            ->latest('start_at')
            ->paginate(20);

        return view('default.panel.user.work.shifts.index', compact('shifts'));
    }

    public function create(Request $request): View
    {
        $users = User::query()->where('company_id', $request->user()->company_id)->get();
        $jobs = ServiceJob::query()->where('company_id', $request->user()->company_id)->get();

        return view('default.panel.user.work.shifts.create', compact('users', 'jobs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id'        => ['required', 'exists:users,id'],
            'service_job_id' => ['nullable', 'exists:service_jobs,id'],
            'start_at'       => ['required', 'date'],
            'end_at'         => ['required', 'date', 'after:start_at'],
            'status'         => ['required', 'string'],
        ]);

        $data['company_id'] = $request->user()->company_id;

        Shift::create($data);

        return redirect()->route('dashboard.work.shifts.index')->with('message', __('Shift created'));
    }

    public function show(Request $request, Shift $shift): View
    {
        $this->authorize('view', $shift);

        return view('default.panel.user.work.shifts.show', compact('shift'));
    }
}
