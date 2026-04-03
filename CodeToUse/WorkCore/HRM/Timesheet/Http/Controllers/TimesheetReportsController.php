<?php

namespace Modules\Timesheet\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Timesheet\Services\Reports\TimesheetReportService;
use Modules\Timesheet\Services\TimesheetIntegrationResolver;

class TimesheetReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    protected function authorizePermission(string $perm): void
    {
        if (function_exists('isAbleTo') && isAbleTo($perm)) {
            return;
        }
        // Fallback: many Worksuite installs attach permissions to user model.
        $user = Auth::user();
        if ($user && method_exists($user, 'isAbleTo') && $user->isAbleTo($perm)) {
            return;
        }
        abort(403);
    }

    public function dashboard(Request $request, TimesheetReportService $reports)
    {
        $this->authorizePermission('timesheet report');

        $companyId = function_exists('company') && company() ? (int) company()->id : null;

        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::now()->endOfMonth();

        $summary = $reports->summaryForRange($companyId, null, $from, $to);
        $byProject = $reports->breakdownByProject($from, $to, $companyId);
        $byWorkOrder = $reports->breakdownByWorkOrder($from, $to, $companyId);

        return view('timesheet::reports.dashboard', compact('from','to','summary','byProject','byWorkOrder'));
    }

    public function workOrders(Request $request, TimesheetReportService $reports, TimesheetIntegrationResolver $resolver)
    {
        $this->authorizePermission('timesheet report');

        $companyId = function_exists('company') && company() ? (int) company()->id : null;
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::now()->endOfMonth();

        $rows = $reports->breakdownByWorkOrder($from, $to, $companyId);

        // Enrich labels (optional)
        $wop = $resolver->workOrderProvider();
        foreach ($rows as &$r) {
            $r['label'] = $wop->label($companyId, $r['work_order_id']) ?? (string) $r['work_order_id'];
        }

        return view('timesheet::reports.work_orders', compact('from','to','rows'));
    }

    public function crew(Request $request, TimesheetReportService $reports)
    {
        $this->authorizePermission('timesheet report');

        $companyId = function_exists('company') && company() ? (int) company()->id : null;
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::now()->endOfMonth();

        $rows = $reports->breakdownByUser($from, $to, $companyId);

        return view('timesheet::reports.crew', compact('from','to','rows'));
    }

    public function projects(Request $request, TimesheetReportService $reports)
    {
        $this->authorizePermission('timesheet report');

        $companyId = function_exists('company') && company() ? (int) company()->id : null;
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::now()->endOfMonth();

        $rows = $reports->breakdownByProject($from, $to, $companyId);

        return view('timesheet::reports.projects', compact('from','to','rows'));
    }
}
