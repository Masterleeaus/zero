<?php

namespace App\Http\Controllers;

use App\DataTables\AttendanceReportDataTable;
use App\Models\User;

class AttendanceReportController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.attendanceReport';
    }

    public function index(AttendanceReportDataTable $dataTable)
    {
        abort_403(user()->permission('view_attendance_report') != 'all');

        if (!request()->ajax()) {
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone);
            $this->cleaners = User::allEmployees();
        }

        return $dataTable->render('reports.attendance.index', $this->data);
    }

}
