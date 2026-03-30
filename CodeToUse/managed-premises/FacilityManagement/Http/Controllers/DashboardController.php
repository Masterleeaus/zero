<?php
namespace Modules\FacilityManagement\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\FacilityManagement\Entities\{Site,Building,Unit,Asset,Inspection,Doc,Occupancy};

class DashboardController extends Controller {
  public function index() {
    $metrics = $this->counts();
    return view('facility::dashboard', compact('metrics'));
  }

  protected function counts(): array {
    return [
      'sites'       => Site::query()->count(),
      'buildings'   => Building::query()->count(),
      'units'       => Unit::query()->count(),
      'assets'      => Asset::query()->count(),
      'ins_due'     => Inspection::query()->where('status','scheduled')->whereNotNull('scheduled_at')->where('scheduled_at','<=', now()->addDays(7))->count(),
      'docs_exp'    => Doc::query()->whereNotNull('expires_at')->whereDate('expires_at','<=', now()->addDays(30))->count(),
      'occupied'    => Occupancy::query()->where('status','active')->count(),
      'vacant'      => max(0, (int)Unit::query()->count() - (int)Occupancy::query()->where('status','active')->distinct('unit_id')->count('unit_id')),
    ];
  }
}
