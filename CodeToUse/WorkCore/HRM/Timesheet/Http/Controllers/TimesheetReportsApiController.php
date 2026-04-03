<?php

namespace Modules\Timesheet\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Timesheet\Services\Reports\TimesheetReportService;

class TimesheetReportsApiController extends Controller
{
    protected function guard(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::user()->isAbleTo('timesheet report'), 403);
    }

    public function summary(Request $request, TimesheetReportService $reports)
    {
        $this->guard();

        $companyId = function_exists('company') && company() ? (int) company()->id : null;

        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::now()->endOfMonth();

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => $reports->summaryForRange($companyId, null, $from, $to),
        ]);
    }

    public function byProject(Request $request, TimesheetReportService $reports)
    {
        $this->guard();

        $companyId = function_exists('company') && company() ? (int) company()->id : null;

        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::now()->endOfMonth();

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'rows' => $reports->breakdownByProject($from, $to, $companyId),
        ]);
    }

    public function byUser(Request $request, TimesheetReportService $reports)
    {
        $this->guard();

        $companyId = function_exists('company') && company() ? (int) company()->id : null;

        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::now()->endOfMonth();

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'rows' => $reports->breakdownByUser($from, $to, $companyId),
        ]);
    }

    public function byWorkOrder(Request $request, TimesheetReportService $reports)
    {
        $this->guard();

        $companyId = function_exists('company') && company() ? (int) company()->id : null;

        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::now()->endOfMonth();

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'rows' => $reports->breakdownByWorkOrder($from, $to, $companyId),
        ]);
    }
}
