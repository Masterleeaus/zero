<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function create(Order $order, array $data, array $items): SaleReturn
    {
        return DB::transaction(function () use ($order, $data, $items) {
            $saleReturn = SaleReturn::create([
                'store_id' => $order->store_id,
                'order_id' => $order->id,
                'return_number' => SaleReturn::generateNumber(),
                'total_refund_amount' => 0,
                'status' => 'pending',
                'reason' => $data['reason'] ?? 'Customer Request',
                'notes' => $data['notes'] ?? null,
            ]);

            $totalRefund = 0;
            foreach ($items as $itemData) {
                // Ensure the item belongs to the order
                $orderItem = OrderItem::where('order_id', $order->id)->where('id', $itemData['order_item_id'])->firstOrFail();
                
                // Don't allow returning more than ordered (simplified check)
                $qty = min($itemData['quantity'], $orderItem->quantity);
                $refund = ($orderItem->price * $qty);

                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'quantity' => $qty,
                    'refund_amount' => $refund,
                    'condition' => $itemData['condition'] ?? 'good',
                ]);

                $totalRefund += $refund;
            }

            $saleReturn->update(['total_refund_amount' => $totalRefund]);
            return $saleReturn->fresh('items.product');
        });
    }

    public function approveAndRefund(SaleReturn $saleReturn, ?int $userId = null)
    {
        return DB::transaction(function () use ($saleReturn, $userId) {
            $saleReturn->load('items.product');

            foreach ($saleReturn->items as $item) {
                // Only return to stock if condition is good
                if ($item->condition === 'good') {
                    $item->product->increment('stock', $item->quantity);

                    // Record stock movement
                    StockMovement::create([
                        'store_id' => $saleReturn->store_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => null, // Simplified for now
                        'user_id' => $userId,
                        'type' => 'customer_return',
                        'quantity' => $item->quantity,
                        'reference' => $saleReturn->return_number,
                        'notes' => 'Stock returned from order: ' . $saleReturn->order->order_number,
                    ]);
                }
            }

            // Update original order status if needed (e.g., refunded, partially_refunded)
            // Here we just mark the return as refunded
            $saleReturn->update(['status' => 'refunded']);

            return $saleReturn;
        });
    }

    public function reject(SaleReturn $saleReturn)
    {
        $saleReturn->update(['status' => 'rejected']);
        return $saleReturn;
    }
}
