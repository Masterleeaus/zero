<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\StaffProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffProfileController extends CoreController
{
    public function index(Request $request): View
    {
        $profiles = StaffProfile::query()
            ->with('user')
            ->paginate(15);

        return $this->placeholder('Staff Profiles', 'Manage your team\'s employment records.');
    }

    public function show(Request $request, StaffProfile $staffProfile): View
    {
        abort_if($staffProfile->company_id !== $request->user()?->company_id, 403);

        $staffProfile->load(['user', 'manager']);

        return $this->placeholder('Staff Profile', $staffProfile->user?->name ?? '');
    }

    public function create(Request $request): View
    {
        return $this->placeholder('Create Staff Profile', 'Add a new staff profile.');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()?->company_id;

        $validated = $request->validate([
            'user_id'                  => ['required', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'employee_number'          => ['nullable', 'string', 'max:50'],
            'job_title'                => ['nullable', 'string', 'max:100'],
            'department'               => ['nullable', 'string', 'max:100'],
            'employment_type'          => ['nullable', 'string', 'in:full_time,part_time,casual,contract'],
            'start_date'               => ['nullable', 'date'],
            'end_date'                 => ['nullable', 'date', 'after_or_equal:start_date'],
            'hourly_rate'              => ['nullable', 'numeric', 'min:0'],
            'salary'                   => ['nullable', 'numeric', 'min:0'],
            'pay_frequency'            => ['nullable', 'string', 'max:50'],
            'manager_id'               => ['nullable', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'emergency_contact_name'   => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone'  => ['nullable', 'string', 'max:30'],
            'notes'                    => ['nullable', 'string', 'max:2000'],
            'status'                   => ['nullable', 'string', 'in:active,inactive,terminated'],
        ]);

        StaffProfile::query()->create([
            'company_id' => $request->user()?->company_id,
            ...$validated,
        ]);

        return redirect()->route('dashboard.team.staff-profiles.index')->with([
            'type'    => 'success',
            'message' => __('Staff profile created.'),
        ]);
    }

    public function edit(Request $request, StaffProfile $staffProfile): View
    {
        abort_if($staffProfile->company_id !== $request->user()?->company_id, 403);

        return $this->placeholder('Edit Staff Profile', $staffProfile->user?->name ?? '');
    }

    public function update(Request $request, StaffProfile $staffProfile): RedirectResponse
    {
        abort_if($staffProfile->company_id !== $request->user()?->company_id, 403);

        $companyId = $request->user()?->company_id;

        $validated = $request->validate([
            'employee_number'          => ['nullable', 'string', 'max:50'],
            'job_title'                => ['nullable', 'string', 'max:100'],
            'department'               => ['nullable', 'string', 'max:100'],
            'employment_type'          => ['nullable', 'string', 'in:full_time,part_time,casual,contract'],
            'start_date'               => ['nullable', 'date'],
            'end_date'                 => ['nullable', 'date', 'after_or_equal:start_date'],
            'hourly_rate'              => ['nullable', 'numeric', 'min:0'],
            'salary'                   => ['nullable', 'numeric', 'min:0'],
            'pay_frequency'            => ['nullable', 'string', 'max:50'],
            'manager_id'               => ['nullable', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'emergency_contact_name'   => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone'  => ['nullable', 'string', 'max:30'],
            'notes'                    => ['nullable', 'string', 'max:2000'],
            'status'                   => ['nullable', 'string', 'in:active,inactive,terminated'],
        ]);

        $staffProfile->update($validated);

        return back()->with([
            'type'    => 'success',
            'message' => __('Staff profile updated.'),
        ]);
    }

    public function destroy(Request $request, StaffProfile $staffProfile): RedirectResponse
    {
        abort_if($staffProfile->company_id !== $request->user()?->company_id, 403);

        $staffProfile->delete();

        return redirect()->route('dashboard.team.staff-profiles.index')->with([
            'type'    => 'success',
            'message' => __('Staff profile deleted.'),
        ]);
    }
}
