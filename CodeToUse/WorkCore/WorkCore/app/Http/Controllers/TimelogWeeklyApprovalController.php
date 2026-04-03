<?php

namespace App\Http\Controllers;

use App\DataTables\TimeLogWeeklyApprovalReportDataTable;
use App\DataTables\TimeLogPendingWeeklyApprovalReportDataTable;
use App\DataTables\TimeLogConsolidatedReportDataTable;
use App\DataTables\TimeLogProjectwiseReportDataTable;
use App\Exports\ProjectwiseTimeLogExport;
use App\Helper\Reply;
use App\Models\Site;
use App\Models\ProjectTimeLog;
use App\Models\Service Job;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TimelogWeeklyApprovalController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.weeklyTimehsheet';
        $this->pageIcon = 'ti-pie-chart';

        
        /*
        TODO
            1.permission for both reports

        */
    }

    public function index(TimeLogWeeklyApprovalReportDataTable $dataTable)
    {
        abort_403(user()->permission('view_time_log_report') != 'all');

        if (!request()->ajax()) {
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone);

            $this->cleaners = User::allEmployees();
            $this->customers = User::allClients();
            $this->sites = Site::allProjects();
            $this->service jobs = Service Job::all();
        }

        return $dataTable->render('reports.timelogs.weekly-timelog-index', $this->data);
    }

    public function pendingTimelogReportIndex(TimeLogPendingWeeklyApprovalReportDataTable $dataTable)
    {
        abort_403(user()->permission('view_time_log_report') != 'all');
        
        $this->pageTitle = 'app.menu.weeklypPendingTimehsheet';

        if (!request()->ajax()) {
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone);

            $this->cleaners = User::whereHas('reportingTeam', function ($query)  {
            }, '>', 0)->get();
           
        
            $this->customers = User::allClients();
            $this->sites = Site::allProjects();
            $this->service jobs = Service Job::all();
        }

        return $dataTable->render('reports.timelogs.weekly-pending-timelog-index', $this->data);
    }

    private function formatTime($totalMinutes)
    {
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return $hours > 0
            ? $hours . 'h' . ($minutes > 0 ? ' ' . sprintf('%02dm', $minutes) : '')
            : ($minutes > 0 ? sprintf('%dm', $minutes) : '0s');
    }


}
