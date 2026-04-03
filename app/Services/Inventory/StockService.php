<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function onHand(int $itemId, ?int $warehouseId = null): int
    {
        $query = StockMovement::withoutGlobalScopes()
            ->where('item_id', $itemId);

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        return (int) $query->sum('qty_change');
    }

    public function recordMovement(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $movement = StockMovement::create($data);

            InventoryItem::withoutGlobalScopes()
                ->where('id', $data['item_id'])
                ->increment('qty_on_hand', $data['qty_change']);

            return $movement;
        });
    }

    /**
     * Adjust stock for an item to a target quantity.
     *
     * @param  array{item_id: int, warehouse_id: int, target_qty: int, note: string, company_id?: int, created_by?: int}  $data
     */
    public function adjustStock(array $data): StockMovement
    {
        $itemId      = (int) $data['item_id'];
        $warehouseId = (int) $data['warehouse_id'];
        $targetQty   = (int) $data['target_qty'];
        $note        = (string) ($data['note'] ?? '');

        $current    = $this->onHand($itemId, $warehouseId);
        $difference = $targetQty - $current;

        return $this->recordMovement(array_merge(
            [
                'item_id'      => $itemId,
                'warehouse_id' => $warehouseId,
                'type'         => 'adjust',
                'qty_change'   => $difference,
                'note'         => $note,
                'reference'    => 'ADJ-' . now()->format('YmdHis'),
            ],
            array_filter([
                'company_id' => $data['company_id'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ], static fn ($v) => $v !== null),
        ));
    }
}
