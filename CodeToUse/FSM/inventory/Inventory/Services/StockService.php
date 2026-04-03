<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Entities\StockMovement;

class StockService
{
    public function onHand(int $itemId, ?int $warehouseId = null): int
    {
        $q = StockMovement::query()->where('item_id', $itemId);
        if ($warehouseId) { $q->where('warehouse_id', $warehouseId); }
        return (int) $q->sum('qty_change');
    }
}
