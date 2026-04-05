<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Events\Inventory\InventoryLowStockDetected;
use App\Events\Inventory\StockVarianceDetected;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Stocktake;
use Illuminate\Support\Facades\Event;

class ReorderSignalService
{
    /**
     * Scan all items for a company and emit low-stock signals.
     * Returns array of low-stock item payloads.
     */
    public function detectLowStock(int $companyId): array
    {
        $lowStockItems = InventoryItem::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('track_quantity', true)
            ->where('status', 'active')
            ->whereColumn('qty_on_hand', '<=', 'reorder_point')
            ->get();

        $signals = [];

        foreach ($lowStockItems as $item) {
            $item->update(['low_stock_flag' => true]);

            $signal = [
                'item_id'               => $item->id,
                'item_name'             => $item->name,
                'sku'                   => $item->sku,
                'qty_on_hand'           => $item->qty_on_hand,
                'reorder_point'         => $item->reorder_point,
                'reorder_qty'           => $item->reorder_qty,
                'min_stock'             => $item->min_stock,
                'preferred_supplier_id' => $item->preferred_supplier_id,
                'severity'              => $item->qty_on_hand <= 0 ? 'critical' : 'warning',
            ];

            Event::dispatch(new InventoryLowStockDetected($companyId, $signal));
            $signals[] = $signal;
        }

        // Reset flag for items that have recovered above threshold
        InventoryItem::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('track_quantity', true)
            ->where('low_stock_flag', true)
            ->whereColumn('qty_on_hand', '>', 'reorder_point')
            ->update(['low_stock_flag' => false]);

        return $signals;
    }

    /**
     * Detect variance signals from a finalized stocktake.
     */
    public function detectVariances(Stocktake $stocktake): array
    {
        $variances = [];

        foreach ($stocktake->lines as $line) {
            $variance = $line->counted_qty - $line->expected_qty;

            if ($variance !== 0) {
                $signal = [
                    'stocktake_id' => $stocktake->id,
                    'item_id'      => $line->item_id,
                    'item_name'    => $line->item?->name,
                    'expected_qty' => $line->expected_qty,
                    'counted_qty'  => $line->counted_qty,
                    'variance'     => $variance,
                    'severity'     => abs($variance) > 10 ? 'high' : 'medium',
                ];

                Event::dispatch(new StockVarianceDetected($stocktake->company_id, $signal));
                $variances[] = $signal;
            }
        }

        return $variances;
    }
}
