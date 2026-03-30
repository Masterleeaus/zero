<?php

namespace Modules\FacilityManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\FacilityManagement\Entities\{Building};

class BuildingEnergyController extends Controller
{
    public function show($id)
    {
        $building = Building::findOrFail($id);
        $from = now()->startOfMonth()->subMonths(11);

        $types = ['power','water','gas'];
        $series = [];
        foreach ($types as $t) {
            $rows = DB::table('facility_meter_reads as mr')
                ->selectRaw("DATE_FORMAT(mr.read_at, '%Y-%m') as ym, SUM(mr.reading) as total")
                ->join('facility_meters as m','m.id','=','mr.meter_id')
                ->join('units as u','u.id','=','m.unit_id')
                ->where('u.building_id',$id)
                ->where('m.meter_type',$t)
                ->whereNotNull('mr.read_at')
                ->where('mr.read_at','>=',$from)
                ->groupBy('ym')->orderBy('ym')->get();
            $series[$t] = array_map(fn($r)=>['ym'=>$r->ym,'total'=>(float)$r->total], $rows->all());
        }

        $months = [];
        for ($i=11;$i>=0;$i--) $months[] = now()->startOfMonth()->subMonths($i)->format('Y-m');

        return view('facility::buildings.energy', compact('building','series','months'));
    }
}
