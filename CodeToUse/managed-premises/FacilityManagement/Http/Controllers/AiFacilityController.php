<?php
namespace Modules\FacilityManagement\Http\Controllers;
use Illuminate\Http\Request; use Illuminate\Routing\Controller;
use Modules\FacilityManagement\Services\AiClient;

class AiFacilityController extends Controller {
  public function unitChecklist($id, Request $r, AiClient $ai){
    $txt = $ai->generate('inspection-checklist', ['type'=>'unit','id'=>$id,'notes'=>$r->input('notes','')]);
    return response()->json(['ok'=>true,'text'=>$txt]);
  }
  public function assetPmPlan($id, Request $r, AiClient $ai){
    $txt = $ai->generate('pm-plan', ['type'=>'asset','id'=>$id,'notes'=>$r->input('notes','')]);
    return response()->json(['ok'=>true,'text'=>$txt]);
  }
  public function docSummary($id, Request $r, AiClient $ai){
    $txt = $ai->generate('doc-summary', ['type'=>'doc','id'=>$id,'notes'=>$r->input('notes','')]);
    return response()->json(['ok'=>true,'text'=>$txt]);
  }
}