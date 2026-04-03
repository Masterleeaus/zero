<?php
namespace Modules\FacilityManagement\Http\Controllers\Api;
use Illuminate\Http\Request; use Illuminate\Routing\Controller;

class FacilityApiController extends Controller {
  public function sites(){ return response()->json(['data'=>[]]); }
  public function completeInspection($id){ return response()->json(['ok'=>true,'id'=>$id]); }
  public function readMeter($id){ return response()->json(['ok'=>true,'id'=>$id]); }
  public function import(Request $r){ return response()->json(['ok'=>true,'rows'=>0]); }
}
