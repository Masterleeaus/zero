<?php

namespace Modules\Inventory\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Entities\{Stocktake,StocktakeLine,InventoryAudit};
use Modules\Inventory\Services\ReconciliationService;\nuse Modules\Inventory\Services\NotificationService;

class StocktakesApiController extends Controller
{
    public function index() {
        return Stocktake::orderByDesc('id')->paginate(50);
    }
    public function show($id) {
        return Stocktake::with('lines')->findOrFail($id);
    }
    public function store(Request $r) {
        $data = $r->validate(['warehouse_id'=>'nullable|integer','ref'=>'nullable|string']);
        $st = Stocktake::create($data);
        return response()->json($st, 201);
    }
    public function finalize($id) {
        // PREVIEW GATE
        if (config('inventory.reconciliation.require_preview')) {
            $key = 'inventory.preview.' . $id . '.' . optional(request()->user())->id;
            $ts = cache()->get($key);
            if (!$ts) { return response()->json(['error'=>'Preview required before finalize'], 428); }
        }
        $svc = new ReconciliationService();
        $st = Stocktake::findOrFail($id);
        $st->status = 'final';
        $st->save();
                $adjustments = $svc->reconcileStocktake($st);
                InventoryAudit::create(['action'=>'stocktake_finalize','context'=>['stocktake_id'=>$st->id, 'adjustments'=>$adjustments]]);
        (new NotificationService())->stocktakeFinalized(['stocktake_id'=>$st->id,'adjustments'=>$adjustments]);
        return ['ok'=>true, 'id'=>$st->id, 'adjustments'=>$adjustments];
    }
}
