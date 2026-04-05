<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\PurchaseOrderItem;

class ReorderRecommendationService
{
    /**
     * Generate reorder recommendations for a company.
     * Returns array of recommendation payloads (NOT auto-executed).
     */
    public function generateRecommendations(int $companyId): array
    {
        $lowItems = InventoryItem::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('track_quantity', true)
            ->where('status', 'active')
            ->whereRaw('qty_on_hand <= reorder_point')
            ->get();

        $recommendations = [];

        foreach ($lowItems as $item) {
            $openPOQty = PurchaseOrderItem::withoutGlobalScopes()
                ->where('item_id', $item->id)
                ->whereHas('purchaseOrder', fn ($q) => $q
                    ->where('company_id', $companyId)
                    ->whereIn('status', ['draft', 'sent', 'partial'])
                )
                ->selectRaw('SUM(qty_ordered - qty_received) as pending_qty')
                ->value('pending_qty') ?? 0;

            $shortfall    = max(0, $item->reorder_point - $item->qty_on_hand - (int) $openPOQty);
            $suggestedQty = max($item->reorder_qty, $shortfall);

            if ($suggestedQty <= 0 && (int) $openPOQty > 0) {
                // Already on order — no new recommendation needed
                continue;
            }

            $recommendations[] = [
                'item_id'               => $item->id,
                'item_name'             => $item->name,
                'sku'                   => $item->sku,
                'qty_on_hand'           => $item->qty_on_hand,
                'reorder_point'         => $item->reorder_point,
                'min_stock'             => $item->min_stock,
                'open_po_qty'           => (int) $openPOQty,
                'suggested_order_qty'   => $suggestedQty,
                'preferred_supplier_id' => $item->preferred_supplier_id,
                'preferred_supplier'    => $item->preferredSupplier?->name,
                'estimated_cost'        => round($suggestedQty * (float) $item->cost_price, 2),
                'action'                => 'create_purchase_order',
                'priority'              => $item->qty_on_hand <= 0
                    ? 'critical'
                    : ($item->qty_on_hand <= $item->min_stock ? 'high' : 'medium'),
            ];
        }

        usort($recommendations, static function ($a, $b) {
            $order = ['critical' => 0, 'high' => 1, 'medium' => 2];
            return ($order[$a['priority']] ?? 2) <=> ($order[$b['priority']] ?? 2);
        });

        return $recommendations;
    }
}
