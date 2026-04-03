<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Entities\{Stocktake,StocktakeLine,Item,Warehouse,InventoryAudit};
use Modules\Inventory\Services\ReconciliationService;\nuse Modules\Inventory\Services\NotificationService;

class StocktakeWebController extends Controller
{
    public function index() {
        $rows = Stocktake::with('warehouse')->orderByDesc('id')->paginate(20);
        return view('inventory::stocktakes.index', compact('rows'));
    }
    public function create() {
        return view('inventory::stocktakes.create', ['warehouses'=>Warehouse::orderBy('name')->get()]);
    }
    public function store(Request $r) {
        $d = $r->validate(['warehouse_id'=>'nullable|integer','ref'=>'nullable|string']);
        $st = Stocktake::create($d);
        return redirect()->route('inventory.st.edit', $st);
    }
    public function edit(Stocktake $stocktake) {
        return view('inventory::stocktakes.edit', ['stocktake'=>$stocktake->load('lines.item'),'items'=>Item::orderBy('name')->get()]);
    }
    public function addLine(Stocktake $stocktake, Request $r) {
        $d = $r->validate(['item_id'=>'required|integer','counted_qty'=>'required|integer']);
        $line = $stocktake->lines()->create($d);
        return back()->with('ok','Line added');
    }
    public function finalize(Stocktake $stocktake) {
        // PREVIEW GATE
        if (config('inventory.reconciliation.require_preview')) {
            $ts = session('inventory.preview.' . $stocktake->id);
            $ttl = (int) config('inventory.reconciliation.preview_ttl_minutes', 15);
            if (!$ts || now()->diffInMinutes($ts) > $ttl) {
                return redirect()->route('inventory.st.preview', $stocktake)->with('ok','Please review the reconciliation preview before finalizing.');
            }
        }

        $svc = new ReconciliationService();
        $stocktake->update(['status'=>'final']);
                $adjustments = $svc->reconcileStocktake($stocktake);
                InventoryAudit::create(['action'=>'stocktake_finalize','context'=>['stocktake_id'=>$stocktake->id, 'adjustments'=>$adjustments]]);
        (new NotificationService())->stocktakeFinalized(['stocktake_id'=>$stocktake->id,'adjustments'=>$adjustments]);
        return redirect()->route('inventory.st.index')->with('ok','Stocktake finalized');
    }
    public function exportCsv(Stocktake $stocktake) {
        $csv = "item_id,item_name,counted_qty\n";
        foreach($stocktake->lines()->with('item')->get() as $ln){
            $csv .= $ln->item_id . "," . ($ln->item->name ?? '') . "," . $ln->counted_qty . "\n";
        }
        return response($csv,200,['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename="stocktake_'.$stocktake->id.'.csv"']);
    }
    public function importCsv(Stocktake $stocktake, Request $r) {
        $r->validate(['csv'=>'required|file']);
        $rows = array_map('str_getcsv', file($r->file('csv')->getRealPath()));
        foreach(array_slice($rows,1) as $row){
            if (count($row) >= 3){
                $stocktake->lines()->updateOrCreate(['item_id'=>(int)$row[0]], ['counted_qty'=>(int)$row[2]]);
            }
        }
        return back()->with('ok','CSV imported');
    }
}

    public function trashed() {
        \Illuminate\Support\Facades\Gate::authorize('inventory.view');
        $rows = \Modules\Inventory\Entities\Stocktake::onlyTrashed()->orderByDesc('deleted_at')->paginate(20);
        return view('inventory::stocktakes.trashed', ['rows'=>$rows]);
    }
    public function restore($id) {
        \Illuminate\Support\Facades\Gate::authorize('inventory.manage');
        $row = \Modules\Inventory\Entities\Stocktake::onlyTrashed()->findOrFail($id);
        $row->restore();
        return redirect()->route('inventory.st.trashed')->with('ok','Stocktake restored');
    }
    public function bulkDelete(\Illuminate\Http\Request $r) {
        \Illuminate\Support\Facades\Gate::authorize('inventory.manage');
        $ids = array_filter((array) $r->input('ids', []));
        if ($ids) { \Modules\Inventory\Entities\Stocktake::whereIn('id', $ids)->delete(); }
        return back()->with('ok','Selected stocktakes moved to trash');
    }
    public function bulkRestore(\Illuminate\Http\Request $r) {
        \Illuminate\Support\Facades\Gate::authorize('inventory.manage');
        $ids = array_filter((array) $r->input('ids', []));
        if ($ids) { \Modules\Inventory\Entities\Stocktake::onlyTrashed()->whereIn('id', $ids)->restore(); }
        return back()->with('ok','Selected stocktakes restored');
    }
