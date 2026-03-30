<?php

namespace Modules\FacilityManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\FacilityManagement\Entities\{Building, Unit, Meter, MeterRead, Inspection, Occupancy};

class ReportsController extends Controller
{
    // CSV: energy by building per month for last N months
    public function buildingEnergyCsv(Request $r)
    {
        $months = (int)($r->input('months', 12));
        $type   = $r->input('meter_type', 'power');
        $from   = now()->startOfMonth()->subMonths($months-1);

        $rows = DB::table('facility_meter_reads as mr')
            ->selectRaw("b.id as building_id, b.name as building, DATE_FORMAT(mr.read_at, '%Y-%m') as ym, SUM(mr.reading) as total")
            ->join('facility_meters as m', 'm.id', '=', 'mr.meter_id')
            ->leftJoin('units as u', 'u.id', '=', 'm.unit_id')
            ->leftJoin('buildings as b', 'b.id', '=', 'u.building_id')
            ->when($type !== '*', fn($q)=>$q->where('m.meter_type',$type))
            ->whereNotNull('mr.read_at')
            ->where('mr.read_at','>=',$from)
            ->groupBy('b.id','b.name','ym')
            ->orderBy('b.name')->orderBy('ym')
            ->get();

        $resp = new StreamedResponse(function() use ($rows){
            $out = fopen('php://output', 'w');
            fputcsv($out, ['building_id','building','month','total_reading']);
            foreach ($rows as $r) fputcsv($out, [$r->building_id, $r->building, $r->ym, $r->total]);
            fclose($out);
        });
        $resp->headers->set('Content-Type','text/csv');
        $resp->headers->set('Content-Disposition','attachment; filename="building-energy.csv"');
        return $resp;
    }

    // CSV: SLA for inspections (avg hours to complete; overdue rate)
    public function slaCsv(Request $r)
    {
        $since = $r->input('since'); // optional YYYY-MM-DD
        $q = Inspection::query()->where('status','completed')->whereNotNull('scheduled_at')->whereNotNull('completed_at');
        if ($since) $q->whereDate('scheduled_at','>=',$since);
        $rows = $q->selectRaw("DATE_FORMAT(scheduled_at, '%Y-%m') as ym, AVG(TIMESTAMPDIFF(HOUR, scheduled_at, completed_at)) as avg_hours, SUM(CASE WHEN completed_at>scheduled_at THEN 1 ELSE 0 END)/COUNT(*) as overdue_rate")
            ->groupBy('ym')->orderBy('ym')->get();

        $resp = new StreamedResponse(function() use ($rows){
            $out = fopen('php://output', 'w');
            fputcsv($out, ['month','avg_hours_to_complete','overdue_rate']);
            foreach ($rows as $r) fputcsv($out, [$r->ym, round($r->avg_hours,2), round($r->overdue_rate,3)]);
            fclose($out);
        });
        $resp->headers->set('Content-Type','text/csv');
        $resp->headers->set('Content-Disposition','attachment; filename="inspection-sla.csv"');
        return $resp;
    }

    // CSV: occupancy rate per building (active occupancies / units)
    public function occupancyCsv(Request $r)
    {
        $rows = DB::table('buildings as b')
            ->leftJoin('units as u','u.building_id','=','b.id')
            ->leftJoin('facility_occupancies as o', function($j){
                $j->on('o.unit_id','=','u.id')->where('o.status','=','active');
            })
            ->selectRaw('b.id as building_id, b.name as building, COUNT(DISTINCT u.id) as units, COUNT(DISTINCT o.id) as active_occupancies')
            ->groupBy('b.id','b.name')
            ->orderBy('b.name')
            ->get();

        $resp = new StreamedResponse(function() use ($rows){
            $out = fopen('php://output', 'w');
            fputcsv($out, ['building_id','building','units','active_occupancies','occupancy_rate']);
            foreach ($rows as $r) {
                $rate = ($r->units > 0) ? round($r->active_occupancies / $r->units, 3) : 0;
                fputcsv($out, [$r->building_id, $r->building, $r->units, $r->active_occupancies, $rate]);
            }
            fclose($out);
        });
        $resp->headers->set('Content-Type','text/csv');
        $resp->headers->set('Content-Disposition','attachment; filename="building-occupancy.csv"');
        return $resp;
    }
}
