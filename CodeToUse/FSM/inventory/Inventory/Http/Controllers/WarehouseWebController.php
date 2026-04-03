<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Entities\Warehouse;\nuse Illuminate\Support\Facades\Gate;

class WarehouseWebController extends Controller
{
    public function index() { Gate::authorize('inventory.view'); $rows = Warehouse::orderBy('name')->paginate(20); return view('inventory::warehouses.index', ['rows'=>$rows]); }
    public function create() { return view('inventory::warehouses.create'); }
    public function store(\Modules\Inventory\Http\Requests\WarehouseRequest $r) {
        $d = $r->validate(['name'=>'required','code'=>'nullable','location'=>'nullable']);
        Warehouse::create($d);
        return redirect()->route('inventory.wh.index')->with('ok','Warehouse created');
    }
    public function edit(Warehouse $warehouse) { return view('inventory::warehouses.edit', compact('warehouse')); }
    public function update(\Modules\Inventory\Http\Requests\WarehouseRequest $r, Warehouse $warehouse) {
        $d = $r->validate(['name'=>'required','code'=>'nullable','location'=>'nullable']);
        $warehouse->update($d);
        return redirect()->route('inventory.wh.index')->with('ok','Warehouse updated');
    }
    public function destroy(Warehouse $warehouse) { $warehouse->delete(); return redirect()->route('inventory.wh.index')->with('ok','Warehouse deleted'); }
}

    public function exportCsv() {
        $rows = \Modules\Inventory\Entities\Warehouse::orderBy('id')->get();
        $csv = "id,name,code,location\n";
        foreach($rows as $r){
            $csv .= str_replace(',',' ', (string) $r->id) . str_replace(',',' ', (string) $r->name) . str_replace(',',' ', (string) $r->code) . str_replace(',',' ', (string) $r->location) . "\n";
        }
        return response($csv,200,['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename="warehouses.csv"']);
    }
    public function importCsv(\Illuminate\Http\Request $r) {
        $r->validate(['csv'=>'required|file']);
        $rows = array_map('str_getcsv', file($r->file('csv')->getRealPath()));
        $header = array_map('trim', $rows[0] ?? []);
        for($i=1;$i<count($rows);$i++){ $row = $rows[$i]; if(!$row) continue;
            $data = array_combine($header, $row);
            \Modules\Inventory\Entities\Warehouse::updateOrCreate(['id'=> (int)($data['id'] ?? 0) ?: null], array_filter($data, fn($v)=>$v!==''));
        }
        return back()->with('ok','CSV imported');
    }

    public function trashed() {
        Gate::authorize('inventory.manage');
        $rows = Warehouse::onlyTrashed()->orderByDesc('deleted_at')->paginate(20);
        return view('inventory::warehouses.trashed', ['rows'=>$rows]);
    }
    public function restore($id) {
        Gate::authorize('inventory.manage');
        $row = Warehouse::onlyTrashed()->findOrFail($id);
        $row->restore();
        return redirect()->route('inventory.wh.trashed')->with('ok','Warehouse restored');
    }
