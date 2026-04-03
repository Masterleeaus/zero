<?php

namespace Modules\Performance\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Performance\Services\Reports\JobPerformanceReportService;

class JobPerformanceReportsController extends Controller
{
    public function index(Request $request, JobPerformanceReportService $svc)
    {
        $filters = $request->only(['from','to','project_id','user_id']);
        $report = $svc->build($filters);

        return view('performance::reports.job-performance', compact('report', 'filters'));
    }
}
