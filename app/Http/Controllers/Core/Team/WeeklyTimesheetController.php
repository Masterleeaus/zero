<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\WeeklyTimesheet;
use App\Services\HRM\TimesheetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WeeklyTimesheetController extends CoreController
{
    public function __construct(private readonly TimesheetService $timesheetService) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $timesheets = WeeklyTimesheet::query()
            ->with('user')
            ->when($status, static fn ($q) => $q->where('status', $status))
            ->latest('week_start')
            ->paginate(15);

        return view('default.panel.user.team.timesheets.index', [
            'timesheets' => $timesheets,
            'filters'    => ['status' => $status],
        ]);
    }

    public function show(Request $request, WeeklyTimesheet $timesheet): View
    {
        abort_if($timesheet->company_id !== $request->user()?->company_id, 403);

        $timesheet->load(['user', 'timelogs']);

        return view('default.panel.user.team.timesheets.show', compact('timesheet'));
    }

    public function submit(Request $request, WeeklyTimesheet $timesheet): RedirectResponse
    {
        abort_if($timesheet->company_id !== $request->user()?->company_id, 403);

        $this->timesheetService->submitWeeklyTimesheet($timesheet, $request->user());

        return back()->with([
            'type'    => 'success',
            'message' => __('Timesheet submitted successfully.'),
        ]);
    }

    public function approve(Request $request, WeeklyTimesheet $timesheet): RedirectResponse
    {
        abort_if($timesheet->company_id !== $request->user()?->company_id, 403);
        abort_if(! $request->user()?->isAdmin(), 403);

        $this->timesheetService->approveTimesheet($timesheet, $request->user());

        return back()->with([
            'type'    => 'success',
            'message' => __('Timesheet approved.'),
        ]);
    }

    public function reject(Request $request, WeeklyTimesheet $timesheet): RedirectResponse
    {
        abort_if($timesheet->company_id !== $request->user()?->company_id, 403);
        abort_if(! $request->user()?->isAdmin(), 403);

        $notes = $request->string('notes')->toString();
        $this->timesheetService->rejectTimesheet($timesheet, $request->user(), $notes);

        return back()->with([
            'type'    => 'success',
            'message' => __('Timesheet rejected.'),
        ]);
    }
}
