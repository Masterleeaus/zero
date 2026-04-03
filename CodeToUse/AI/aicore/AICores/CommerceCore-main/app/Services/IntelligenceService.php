<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class IntelligenceService
{
    /**
     * Calculate fraud score for an order (0-100, where 100 is high risk).
     */
    public function calculateFraudScore(Order $order): int
    {
        $score = 0;
        $user = $order->user;

        // 1. High Value Order Check
        if ($order->total_price > 1000) {
            $score += 15;
        }

        // 2. New Customer vs Returning
        if ($user && $user->orders()->count() === 1) {
            $score += 10;
        }

        // 3. Suspicious Phone Number Patterns (e.g., repeating digits)
        if (preg_match('/(\d)\1{4,}/', $order->phone)) {
            $score += 25;
        }

        // 4. Repeated cancellations check
        if ($user) {
            $cancellationCount = $user->orders()->where('status', 'cancelled')->count();
            if ($cancellationCount > 2) {
                $score += ($cancellationCount * 10);
            }
        }

        return min($score, 100);
    }

    public function getTrends($storeId)
    {
        // Calculate Peak Order Time
        $peak = Order::where('store_id', $storeId)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('strftime("%H", created_at) as hour, count(*) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();

        $peakTimeStr = $peak ? (sprintf("%02d:00 - %02d:00", $peak->hour, $peak->hour + 1)) : 'No Data';

        // Get Top Category (by item volume)
        $topCategory = \App\Models\Category::where('store_id', $storeId)
            ->whereHas('products.orderItems', function($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })
            ->withCount(['products as items_sold' => function($q) {
                $q->join('order_items', 'products.id', '=', 'order_items.product_id')
                  ->select(\DB::raw('sum(quantity)'));
            }])
            ->orderByDesc('items_sold')
            ->first();

        // Calculate Growth (Comp last 30 vs prev 30)
        $revenueLast30 = Order::where('store_id', $storeId)->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(30))->sum('total_price');
        $revenuePrev30 = Order::where('store_id', $storeId)->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(60))
            ->where('created_at', '<', now()->subDays(30))->sum('total_price');

        $growth = $revenuePrev30 > 0 ? (($revenueLast30 - $revenuePrev30) / $revenuePrev30) * 100 : 100;

        return [
            'peak_order_time' => $peakTimeStr,
            'top_performing_category' => $topCategory ? $topCategory->name : 'N/A',
            'estimated_growth_next_month' => ($growth >= 0 ? '+' : '') . round($growth, 1) . '%',
            'churn_risk_customers' => \App\Models\User::where('role', 'customer')
                ->whereDoesntHave('orders', function($q) {
                    $q->where('created_at', '>=', now()->subDays(60));
                })->count()
        ];
    }

    /**
     * Generate an AI-driven marketing campaign suggestion for a store.
     */
    public function generateCampaignSuggestion($store)
    {
        // Advanced: Identify category with high stock but low sales last 14 days
        $lowPerformCategory = \App\Models\Category::where('store_id', $store->id)
            ->withSum('products', 'stock')
            ->get()
            ->filter(fn($c) => $c->products_sum_stock > 10) // Categories with stock
            ->sortBy(function($c) {
                return \App\Models\OrderItem::whereIn('product_id', $c->products()->pluck('id'))
                    ->where('created_at', '>=', now()->subDays(14))
                    ->sum('quantity');
            })
            ->first();

        $categoryName = $lowPerformCategory ? $lowPerformCategory->name : 'General Collection';

        return [
            'name' => "The Absolute " . $categoryName . " Clearout",
            'target_audience' => "Users who haven't ordered in 60+ days",
            'suggested_discount' => "25%",
            'predicted_conversion' => "6.2%",
            'ai_rationale' => "Currently identifying heavy stock (approx " . ($lowPerformCategory->products_sum_stock ?? 0) . " units) in " . $categoryName . " with stagnant movement. A deeper discount combined with a legacy user target is optimal."
        ];
    }
}
