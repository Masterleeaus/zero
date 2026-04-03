<?php

namespace Modules\Performance\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Performance\Services\Reports\SitePerformanceReportService;

class SitePerformanceReportsController extends Controller
{
    public function index(Request $request, SitePerformanceReportService $svc)
    {
        $filters = $request->only(['from','to']);
        $report = $svc->build($filters);

        return view('performance::reports.site-performance', compact('report', 'filters'));
    }
}
