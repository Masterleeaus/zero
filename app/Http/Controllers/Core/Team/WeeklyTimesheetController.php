<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Support\WorkcoreDemoData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WeeklyTimesheetController extends CoreController
{
    public function index(): View
    {
        $status = request()->string('status')->toString();

        $timesheets = WorkcoreDemoData::timesheets()->when($status, static function ($collection) use ($status) {
            return $collection->where('status', $status);
        });

        return view('default.panel.user.team.timesheets.index', [
            'timesheets' => $timesheets,
            'filters'    => ['status' => $status],
        ]);
    }

    public function show(string $timesheet): View
    {
        return view('default.panel.user.team.timesheets.show', [
            'timesheet' => WorkcoreDemoData::timesheets()->firstWhere('number', $timesheet)
                ?? WorkcoreDemoData::timesheets()->first(),
        ]);
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
