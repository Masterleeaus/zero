<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WeeklyTimesheetController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(
            __('Weekly timesheets'),
            __('Submit and review weekly timesheets from WorkCore.')
        );
    }

    public function show(string $timesheet): View
    {
        return $this->placeholder(
            __('Timesheet detail'),
            __('Timesheet :timesheet overview.', ['timesheet' => $timesheet])
        );
    }

    public function submit(Request $request, string $timesheet): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Timesheet :timesheet submitted.', ['timesheet' => $timesheet]),
        ]);
    }

    public function approve(Request $request, string $timesheet): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Timesheet :timesheet approved.', ['timesheet' => $timesheet]),
        ]);
    }

    public function reject(Request $request, string $timesheet): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Timesheet :timesheet rejected.', ['timesheet' => $timesheet]),
        ]);
    }
}

