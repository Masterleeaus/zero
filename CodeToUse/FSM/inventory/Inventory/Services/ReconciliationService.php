<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Entities\{StockMovement, Stocktake, StocktakeLine};
use Modules\Inventory\Services\StockService;

class ReconciliationService
{
    public function reconcileStocktake(Stocktake $stocktake): array
    {
        $svc = new StockService();
        $adjustments = [];
        foreach ($stocktake->lines as $line) {
            $itemId = (int) $line->item_id;
            $whId = $stocktake->warehouse_id ?: null;
            $onHand = $svc->onHand($itemId, $whId);
            $target = (int) $line->counted_qty;
            $diff = $target - $onHand;
            if ($diff !== 0) {
                $rec = StockMovement::create([
                    'item_id' => $itemId,
                    'warehouse_id' => $whId,
                    'type' => 'adjust',
                    'qty_change' => $diff,
                    'note' => 'Stocktake #'.$stocktake->id.' reconciliation'
                ]);
                $adjustments[] = ['item_id'=>$itemId, 'diff'=>$diff, 'movement_id'=>$rec->id];
            }
        }
        return $adjustments;
    }
}
