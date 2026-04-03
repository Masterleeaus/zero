<?php

namespace Modules\Timesheet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Timesheet\Http\Requests\UpdateTimesheetSettingsRequest;
use Modules\Timesheet\Services\CompanySettings;

class TimesheetSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit(CompanySettings $settings)
    {
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        if (method_exists($user, 'isAbleTo') && !$user->isAbleTo('timesheet settings')) {
            abort(403);
        }

        $companyId = function_exists('company') && company() ? company()->id : 0;

        $data = [
            'costing_enabled' => $settings->bool($companyId, 'costing_enabled', config('timesheet.features.costing_enabled', true)),
            'timer_enabled' => $settings->bool($companyId, 'timer_enabled', config('timesheet.features.timer_enabled', true)),
            'approvals_enabled' => $settings->bool($companyId, 'approvals_enabled', config('timesheet.features.approvals_enabled', true)),
        ];

        return view('timesheet::settings.edit', compact('data'));
    }

    public function update(UpdateTimesheetSettingsRequest $request, CompanySettings $settings)
    {
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        if (method_exists($user, 'isAbleTo') && !$user->isAbleTo('timesheet settings')) {
            abort(403);
        }

        $companyId = function_exists('company') && company() ? company()->id : 0;
        $data = $request->validated();

        $settings->set($companyId, 'costing_enabled', $data['costing_enabled'] ? '1' : '0');
        $settings->set($companyId, 'timer_enabled', $data['timer_enabled'] ? '1' : '0');
        $settings->set($companyId, 'approvals_enabled', $data['approvals_enabled'] ? '1' : '0');

        return redirect()->route('timesheet.settings.edit')->with('success', __('Timesheet::timesheet.settings.saved'));
    }
}
