<?php

namespace Modules\FacilityManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\FacilityManagement\Entities\{Unit, Inspection, Doc, MeterRead, Occupancy};

class UnitTimelineController extends Controller
{
    public function show($id)
    {
        $unit = Unit::findOrFail($id);

        $ins = Inspection::where('scope_type','unit')->where('scope_id',$id)
            ->select(['id','status','scheduled_at','completed_at','created_at'])
            ->get()->map(function($i){
                return [
                    'type' => 'inspection',
                    'id'   => $i->id,
                    'at'   => $i->completed_at ?? $i->scheduled_at ?? $i->created_at,
                    'status'=>$i->status
                ];
            })->all();

        $docs = Doc::where('scope_type','unit')->where('scope_id',$id)
            ->select(['id','doc_type','issued_at','expires_at','created_at'])
            ->get()->map(function($d){
                return [
                    'type' => 'doc',
                    'id'   => $d->id,
                    'at'   => $d->issued_at ?? $d->created_at,
                    'status' => 'expires '.$d->expires_at
                ];
            })->all();

        $reads = DB::table('facility_meter_reads as mr')
            ->join('facility_meters as m','m.id','=','mr.meter_id')
            ->where('m.unit_id',$id)
            ->selectRaw("mr.id as id, mr.read_at as at, CONCAT('reading ', mr.reading) as status")
            ->get()->map(function($r){
                return ['type'=>'reading','id'=>$r->id,'at'=>$r->at,'status'=>$r->status];
            })->all();

        $occs = Occupancy::where('unit_id',$id)->get()->map(function($o){
            return ['type'=>'occupancy','id'=>$o->id,'at'=>$o->start_date,'status'=>'start'];
        })->all();

        $events = array_merge($ins,$docs,$reads,$occs);
        usort($events, function($a,$b){ return strcmp((string)$a['at'], (string)$b['at']); });

        return view('facility::units.timeline', compact('unit','events'));
    }
}
