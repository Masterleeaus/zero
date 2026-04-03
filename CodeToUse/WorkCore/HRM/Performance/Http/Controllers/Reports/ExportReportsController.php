<?php

namespace Modules\Performance\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\Performance\Services\Reports\JobPerformanceReportService;
use Modules\Performance\Services\Reports\CallbackTrendReportService;

class ExportReportsController extends Controller
{
    public function jobPerformanceCsv(Request $request, JobPerformanceReportService $svc): StreamedResponse
    {
        $filters = $request->only(['from','to','project_id','user_id']);
        $rows = $svc->exportRows($filters);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_keys($rows[0] ?? ['no_data' => 'no_data']));
            foreach ($rows as $row) fputcsv($out, $row);
            fclose($out);
        }, 'job_performance_report.csv');
    }

    public function callbackTrendsCsv(Request $request, CallbackTrendReportService $svc): StreamedResponse
    {
        $filters = $request->only(['from','to','project_id','user_id']);
        $rows = $svc->exportRows($filters);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_keys($rows[0] ?? ['no_data' => 'no_data']));
            foreach ($rows as $row) fputcsv($out, $row);
            fclose($out);
        }, 'callback_trends_report.csv');
    }
}
