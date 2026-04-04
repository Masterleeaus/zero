<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function create(Store $store, array $data, array $cartItems): Order
    {
        return DB::transaction(function () use ($store, $data, $cartItems) {
            $subtotal = 0;
            $items = [];

            foreach ($cartItems as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal += $product->price * $item['quantity'];
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];

                // Decrease stock
                $product->decrement('stock', $item['quantity']);
            }

            $tax = $subtotal * 0.0; // configurable tax rate
            $total = $subtotal + $tax;

            $order = Order::create([
                'store_id' => $store->id,
                'user_id' => $data['user_id'] ?? null,
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_price' => $total,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }

            return $order->load('items.product');
        });
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);
        return $order->fresh();
    }

    public function getForStore(Store $store, array $filters = [])
    {
        $query = Order::where('store_id', $store->id)->with('items.product');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('customer_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('customer_email', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }
}
