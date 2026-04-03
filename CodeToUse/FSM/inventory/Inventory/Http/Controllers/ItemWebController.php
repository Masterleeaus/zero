<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Entities\Item;\nuse Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Gate;

class ItemWebController extends Controller
{
    public function index(){ Gate::authorize('inventory.view'); {
        $items = Item::orderBy('name')->paginate(20);
        return view('inventory::items.index', compact('items'));
    }
    public function create(){ Gate::authorize('inventory.manage'); { return view('inventory::items.create'); }
    public function store(\Modules\Inventory\Http\Requests\ItemRequest $r) {
        $data = $r->validate(['name'=>'required|string|max:255','sku'=>'nullable|string|max:128','qty'=>'required|integer','category'=>'nullable|string|max:128','unit_price'=>'nullable|numeric']);
        Item::create($data);
        return redirect()->route('inventory.items.index')->with('ok','Item created');
    }
    public function edit(Item $item) { return view('inventory::items.edit', compact('item')); }
    public function update(\Modules\Inventory\Http\Requests\ItemRequest $r, Item $item) {
        $data = $r->validate(['name'=>'required|string|max:255','sku'=>'nullable|string|max:128','qty'=>'required|integer','category'=>'nullable|string|max:128','unit_price'=>'nullable|numeric']);
        $item->update($data);
        return redirect()->route('inventory.items.index')->with('ok','Item updated');
    }
    public function destroy(Item $item) {
        $item->delete();
        return redirect()->route('inventory.items.index')->with('ok','Item deleted');
    }
}

    public function exportCsv() {
        $rows = \Modules\Inventory\Entities\Item::orderBy('id')->get();
        $csv = "id,name,sku,qty,category,unit_price\n";
        foreach($rows as $r){
            $csv .= str_replace(',',' ', (string) $r->id) . str_replace(',',' ', (string) $r->name) . str_replace(',',' ', (string) $r->sku) . str_replace(',',' ', (string) $r->qty) . str_replace(',',' ', (string) $r->category) . str_replace(',',' ', (string) $r->unit_price) . "\n";
        }
        return response($csv,200,['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename="items.csv"']);
    }
    public function importCsv(\Illuminate\Http\Request $r) {
        $r->validate(['csv'=>'required|file']);
        $rows = array_map('str_getcsv', file($r->file('csv')->getRealPath()));
        $header = array_map('trim', $rows[0] ?? []);
        for($i=1;$i<count($rows);$i++){ $row = $rows[$i]; if(!$row) continue;
            $data = array_combine($header, $row);
            \Modules\Inventory\Entities\Item::updateOrCreate(['id'=> (int)($data['id'] ?? 0) ?: null], array_filter($data, fn($v)=>$v!==''));
        }
        return back()->with('ok','CSV imported');
    }

    public function trashed() {
        Gate::authorize('inventory.manage');
        $items = Item::onlyTrashed()->orderByDesc('deleted_at')->paginate(20);
        return view('inventory::items.trashed', compact('items'));
    }
    public function restore($id) {
        Gate::authorize('inventory.manage');
        $item = Item::onlyTrashed()->findOrFail($id);
        $item->restore();
        return redirect()->route('inventory.items.trashed')->with('ok','Item restored');
    }
