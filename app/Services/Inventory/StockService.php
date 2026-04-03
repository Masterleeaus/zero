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

    public function adjustStock(int $itemId, int $warehouseId, int $targetQty, string $note, ?int $companyId = null, ?int $createdBy = null): StockMovement
    {
        $current = $this->onHand($itemId, $warehouseId);
        $difference = $targetQty - $current;

        $data = [
            'item_id'      => $itemId,
            'warehouse_id' => $warehouseId,
            'type'         => 'adjust',
            'qty_change'   => $difference,
            'note'         => $note,
            'reference'    => 'ADJ-' . now()->format('YmdHis'),
        ];

        if ($companyId !== null) {
            $data['company_id'] = $companyId;
        }

        if ($createdBy !== null) {
            $data['created_by'] = $createdBy;
        }

        return $this->recordMovement($data);
    }
}
