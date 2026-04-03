<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Entities\Item;
use Modules\Inventory\Entities\Warehouse;
use Modules\Inventory\Entities\StockMovement;

class MovementWebController extends Controller
{
    public function index() {
        $moves = StockMovement::with(['item','warehouse'])->orderByDesc('id')->paginate(20);
        return view('inventory::movements.index', compact('moves'));
    }
    public function create() {
        return view('inventory::movements.create', ['items'=>Item::orderBy('name')->get(), 'warehouses'=>Warehouse::orderBy('name')->get()]);
    }
    public function store(\Modules\Inventory\Http\Requests\MovementRequest $r) {
        $d = $r->validate(['item_id'=>'required|integer','warehouse_id'=>'nullable|integer','type'=>'required|in:in,out,adjust','qty_change'=>'required|integer','note'=>'nullable|string']);
        StockMovement::create($d);
        return redirect()->route('inventory.moves.index')->with('ok','Movement recorded');
    }
}

    public function trashed() {
        \Illuminate\Support\Facades\Gate::authorize('inventory.view');
        $moves = \Modules\Inventory\Entities\StockMovement::onlyTrashed()->orderByDesc('deleted_at')->paginate(20);
        return view('inventory::movements.trashed', compact('moves'));
    }
    public function restore($id) {
        \Illuminate\Support\Facades\Gate::authorize('inventory.manage');
        $m = \Modules\Inventory\Entities\StockMovement::onlyTrashed()->findOrFail($id);
        $m->restore();
        return redirect()->route('inventory.moves.trashed')->with('ok','Movement restored');
    }
    public function bulkDelete(\Illuminate\Http\Request $r) {
        \Illuminate\Support\Facades\Gate::authorize('inventory.manage');
        $ids = array_filter((array) $r->input('ids', []));
        if ($ids) { \Modules\Inventory\Entities\StockMovement::whereIn('id', $ids)->delete(); }
        return back()->with('ok','Selected movements moved to trash');
    }
    public function bulkRestore(\Illuminate\Http\Request $r) {
        \Illuminate\Support\Facades\Gate::authorize('inventory.manage');
        $ids = array_filter((array) $r->input('ids', []));
        if ($ids) { \Modules\Inventory\Entities\StockMovement::onlyTrashed()->whereIn('id', $ids)->restore(); }
        return back()->with('ok','Selected movements restored');
    }
