<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Create a new purchase order with line items.
     */
    public function create(int $storeId, array $data, array $items): Purchase
    {
        return DB::transaction(function () use ($storeId, $data, $items) {
            $supplier = \App\Models\Supplier::find($data['supplier_id']);
            
            $purchase = Purchase::create([
                'store_id' => $storeId,
                'supplier_id' => $data['supplier_id'],
                'purchase_number' => Purchase::generateNumber(),
                'supplier_name' => $supplier ? $supplier->name : 'Unknown Supplier',
                'supplier_email' => $supplier ? $supplier->email : null,
                'total_amount' => 0,
                'status' => 'pending',
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            $total = 0;
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['unit_cost'];
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $purchase->update(['total_amount' => $total]);
            return $purchase->fresh('items.product');
        });
    }

    /**
     * Mark a purchase as received and update product stock.
     */
    public function receive(Purchase $purchase, ?int $userId = null): Purchase
    {
        return DB::transaction(function () use ($purchase, $userId) {
            $purchase->load('items.product');

            foreach ($purchase->items as $item) {
                // Increase product stock
                $item->product->increment('stock', $item->quantity);

                // Record stock movement
                StockMovement::create([
                    'store_id' => $purchase->store_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'user_id' => $userId,
                    'type' => 'purchase_in',
                    'quantity' => $item->quantity,
                    'reference' => $purchase->purchase_number,
                    'notes' => 'Stock received from supplier: ' . $purchase->supplier_name,
                ]);
            }

            $purchase->update([
                'status' => 'received',
                'received_at' => now(),
            ]);

            return $purchase->fresh();
        });
    }

    /**
     * Cancel a purchase order.
     */
    public function cancel(Purchase $purchase): Purchase
    {
        $purchase->update(['status' => 'cancelled']);
        return $purchase;
    }
}
