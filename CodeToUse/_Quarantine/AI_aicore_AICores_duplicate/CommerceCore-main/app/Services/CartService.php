<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Session;

class CartService
{
    private string $sessionKey;

    public function __construct()
    {
        $this->sessionKey = 'cart';
    }

    public function getItems(string $storeSlug): array
    {
        $cart = Session::get($this->sessionKey . '.' . $storeSlug, []);
        $items = [];

        foreach ($cart as $productId => $data) {
            $product = Product::find($productId);
            if ($product) {
                // Determine price based on purchase type
                $isSubscription = isset($data['purchase_type']) && $data['purchase_type'] === 'subscription';
                $unitPrice = $isSubscription 
                    ? ($product->price * (1 - ($product->subscription_discount_percentage / 100)))
                    : $product->price;

                $items[] = [
                    'product' => $product,
                    'quantity' => $data['quantity'],
                    'purchase_type' => $data['purchase_type'] ?? 'onetime',
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $data['quantity'],
                ];
            }
        }

        return $items;
    }

    public function add(string $storeSlug, int $productId, int $quantity = 1, string $purchaseType = 'onetime'): void
    {
        $key = $this->sessionKey . '.' . $storeSlug . '.' . $productId;
        $current = Session::get($key, ['quantity' => 0, 'purchase_type' => $purchaseType]);
        
        Session::put($key, [
            'quantity' => $current['quantity'] + $quantity,
            'purchase_type' => $purchaseType
        ]);
    }

    public function update(string $storeSlug, int $productId, int $quantity): void
    {
        $key = $this->sessionKey . '.' . $storeSlug . '.' . $productId;
        if ($quantity <= 0) {
            Session::forget($key);
        } else {
            Session::put($key, $quantity);
        }
    }

    public function remove(string $storeSlug, int $productId): void
    {
        Session::forget($this->sessionKey . '.' . $storeSlug . '.' . $productId);
    }

    public function clear(string $storeSlug): void
    {
        Session::forget($this->sessionKey . '.' . $storeSlug);
    }

    public function getTotal(string $storeSlug): float
    {
        $items = $this->getItems($storeSlug);
        return array_sum(array_column($items, 'total'));
    }

    public function getCount(string $storeSlug): int
    {
        $cart = Session::get($this->sessionKey . '.' . $storeSlug, []);
        return collect($cart)->sum('quantity');
    }

    public function toOrderItems(string $storeSlug): array
    {
        $cart = Session::get($this->sessionKey . '.' . $storeSlug, []);
        $items = [];

        foreach ($cart as $productId => $quantity) {
            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        }

        return $items;
    }
}
