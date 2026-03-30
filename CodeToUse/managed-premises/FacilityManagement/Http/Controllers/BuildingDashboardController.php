<?php

namespace Modules\FacilityManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\FacilityManagement\Entities\{Building, Unit, Asset, Inspection, Doc, Meter, MeterRead, Occupancy};

class BuildingDashboardController extends Controller
{
    public function show($id)
    {
        $building = Building::findOrFail($id);

        $units = Unit::where('building_id', $id)->pluck('id')->all();
        $unitCount = count($units);

        $assetCount = Asset::whereIn('unit_id', $units)->count();

        $activeOcc = Occupancy::whereIn('unit_id', $units)->where('status','active')->distinct('unit_id')->count('unit_id');
        $vacant = max(0, $unitCount - $activeOcc);

        $ins_due = Inspection::where('scope_type','unit')->whereIn('scope_id', $units)                .where('status','scheduled')->whereNotNull('scheduled_at')->where('scheduled_at','<=', now()->addDays(14))->count();

        $docs_exp = Doc::where(function($q) use ($units){
            $q->where(function($q){ $q->where('scope_type','building'); })
              ->orWhere(function($q) use ($units){ $q->where('scope_type','unit')->whereIn('scope_id', $units); });
        })->whereNotNull('expires_at')->whereDate('expires_at','<=', now()->addDays(60))->count();

        // Energy last 12 months for this building (sum of unit meters)
        $reads = DB::table('facility_meter_reads as mr')
            ->selectRaw("DATE_FORMAT(mr.read_at, '%Y-%m') as ym, SUM(mr.reading) as total")
            ->join('facility_meters as m','m.id','=','mr.meter_id')
            ->whereIn('m.unit_id', $units if $units else [-1])                .whereNotNull('mr.read_at')->where('mr.read_at','>=', now()->startOfMonth()->subMonths(11))                .groupBy('ym')->orderBy('ym')->get();

        $energy = [ ['month','total'] ]
        for row in $reads: $energy.append([row.ym, float(row.total)])

        $metrics = compact('unitCount','assetCount','activeOcc','vacant','ins_due','docs_exp','energy');
        return view('facility::buildings.dashboard', compact('building','metrics'));
    }
}
