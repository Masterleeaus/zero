<?php

namespace Modules\Timesheet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\Timesheet\Entities\Timesheet;

class TimesheetExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        abort_unless(\Auth::check(), 403);
        abort_unless(\Auth::user()->isAbleTo('timesheet export'), 403);

        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $companyId = company()->id;
        $q = Timesheet::where('company_id', $companyId);
        if ($request->filled('from')) $q->whereDate('date', '>=', $request->input('from'));
        if ($request->filled('to'))   $q->whereDate('date', '<=', $request->input('to'));

        $filename = 'timesheets_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $columns = ['id','user_id','work_order_id','date','clock_in_at','clock_out_at','hours','fsm_rate_per_hour','fsm_overtime_multiplier','fsm_cost_total'];

        return response()->stream(function () use ($q, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            $q->orderBy('date')->chunk(500, function ($chunk) use ($out, $columns) {
                foreach ($chunk as $row) {
                    $data = [];
                    foreach ($columns as $c) $data[] = $row->{$c} ?? '';
                    fputcsv($out, $data);
                }
            });
            fclose($out);
        }, 200, $headers);
    }
}
