<?php

namespace Modules\FacilityManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\FacilityManagement\Entities\{Meter, MeterRead};

class EnergyTrendController extends Controller
{
    // CSV: monthly kWh (or generic 'reading' sum) grouped by YYYY-MM for the last N months
    public function csv(Request $r)
    {
        $months = (int)($r->input('months', 12));
        $type   = $r->input('meter_type', 'power'); // power|water|gas|*
        $from   = now()->startOfMonth()->subMonths($months-1);

        $query = MeterRead::query()
            ->selectRaw("DATE_FORMAT(read_at, '%Y-%m') as ym, SUM(reading) as total")
            ->join('facility_meters as m', 'm.id', '=', 'facility_meter_reads.meter_id')
            ->whereNotNull('read_at')
            ->where('read_at', '>=', $from);

        if ($type !== '*') {
            $query->where('m.meter_type', $type);
        }
        $rows = $query->groupBy('ym')->orderBy('ym')->get();

        $resp = new StreamedResponse(function() use ($rows){
            $out = fopen('php://output', 'w');
            fputcsv($out, ['month','total_reading']);
            foreach ($rows as $r) fputcsv($out, [$r->ym, $r->total]);
            fclose($out);
        });
        $resp->headers->set('Content-Type','text/csv');
        $resp->headers->set('Content-Disposition','attachment; filename="energy-trend.csv"');
        return $resp;
    }

    // SVG sparkline for the same dataset
    public function svg(Request $r)
    {
        $months = (int)($r->input('months', 12));
        $type   = $r->input('meter_type', 'power');
        $from   = now()->startOfMonth()->subMonths($months-1);

        $query = MeterRead::query()
            ->selectRaw("DATE_FORMAT(read_at, '%Y-%m') as ym, SUM(reading) as total")
            ->join('facility_meters as m', 'm.id', '=', 'facility_meter_reads.meter_id')
            ->whereNotNull('read_at')
            ->where('read_at', '>=', $from);
        if ($type !== '*') $query->where('m.meter_type',$type);
        $rows = $query->groupBy('ym')->orderBy('ym')->get();

        $data = [];
        foreach ($rows as $row) $data[] = (float)$row->total;
        if (empty($data)) $data = [0];
        $w = 480; $h = 120; $pad = 10;
        $max = max($data); $min = min($data);
        $spread = max(1e-9, $max-$min);
        $step = count($data) > 1 ? ($w - 2*$pad)/(count($data)-1) : 0;

        $points = [];
        foreach ($data as $i=>$v) {
            $x = $pad + $i*$step;
            $y = $h - $pad - (($v - $min)/$spread) * ($h - 2*$pad);
            $points[] = $x.','.$y;
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'+$w+'" height="'+$h+'">';
        $svg .= '<rect x="0" y="0" width="'+$w+'" height="'+$h+'" fill="white" stroke="#ddd" />';
        $svg .= '<polyline fill="none" stroke="#2a7" stroke-width="2" points="'+implode(' ', $points)+'" />';
        $svg .= '</svg>';

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}
