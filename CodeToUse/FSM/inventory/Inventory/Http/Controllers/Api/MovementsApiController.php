<?php

namespace Modules\Inventory\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Entities\StockMovement;

class MovementsApiController extends Controller
{
    public function index() {
        return StockMovement::orderByDesc('id')->paginate(50);
    }
    public function store(Request $r) {
        $data = $r->validate([
            'item_id' => 'required|integer',
            'warehouse_id' => 'nullable|integer',
            'type' => 'required|in:in,out,adjust',
            'qty_change' => 'required|integer|not_in:0',
            'note' => 'nullable|string'
        ]);
        $rec = StockMovement::create($data);
        return response()->json($rec, 201);
    }
}
