<?php

namespace Modules\Inventory\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Entities\StockMovement;
use Modules\Inventory\Services\StockService;

class StockController extends Controller
{
    public function onHand($itemId, Request $r, StockService $svc) {
        $warehouseId = $r->integer('warehouse_id') ?: null;
        return ['item_id'=>$itemId, 'warehouse_id'=>$warehouseId, 'on_hand'=>$svc->onHand((int)$itemId, $warehouseId)];
    }

    public function move(Request $r) {
        $data = $r->validate([
            'item_id' => 'required|integer',
            'warehouse_id' => 'nullable|integer',
            'type' => 'required|in:in,out,adjust',
            'qty' => 'required|integer|min:1',
            'note' => 'nullable|string'
        ]);
        $qty = (int) $data['qty'];
        $delta = $data['type'] === 'out' ? -$qty : ($data['type'] === 'adjust' ? ($r->integer('qty_change', 0)) : $qty);
        if ($data['type'] !== 'adjust') { $data['qty_change'] = $delta; }
        $rec = StockMovement::create([
            'item_id' => $data['item_id'],
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'type' => $data['type'],
            'qty_change' => $data['qty_change'],
            'note' => $data['note'] ?? null,
        ]);
        return response()->json($rec, 201);
    }
}
