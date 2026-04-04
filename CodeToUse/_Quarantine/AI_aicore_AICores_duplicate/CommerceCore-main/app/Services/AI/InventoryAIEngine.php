<?php

namespace App\Services\AI;

use App\Models\AiInsight;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryAIEngine
{
    /**
     * Analyze inventory for all active stores.
     */
    public function analyzeAll(): void
    {
        Store::where('status', 'active')->chunk(50, function ($stores) {
            foreach ($stores as $store) {
                $this->analyzeStore($store);
            }
        });
    }

    public function analyzeStore(Store $store): void
    {
        $this->detectLowStock($store);
        $this->predictStockouts($store);
        $this->detectDeadStock($store);
    }

    /**
     * Flag products that are below reorder level.
     */
    protected function detectLowStock(Store $store): void
    {
        $lowStockProducts = Product::where('store_id', $store->id)
            ->where('status', 'active')
            ->whereColumn('stock', '<=', DB::raw('COALESCE(reorder_point, 5)'))
            ->where('stock', '>', 0)
            ->get();

        if ($lowStockProducts->isNotEmpty()) {
            AiInsight::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'engine' => 'inventory_ai',
                    'type' => 'low_stock',
                ],
                [
                    'title' => $lowStockProducts->count() . ' Products Low on Stock',
                    'description' => 'The following products are below reorder level: ' . $lowStockProducts->pluck('name')->take(5)->implode(', '),
                    'recommendation' => 'Place purchase orders immediately—especially for your best sellers.',
                    'confidence' => 95,
                    'status' => 'new',
                    'data' => $lowStockProducts->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'stock' => $p->stock,
                    ])->toArray(),
                ]
            );
        }
    }

    /**
     * Predict stockouts based on sales velocity.
     */
    protected function predictStockouts(Store $store): void
    {
        // Calculate average daily sales per product over the last 30 days
        $salesVelocity = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.store_id', $store->id)
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                'products.id',
                'products.name',
                'products.stock',
                DB::raw('SUM(order_items.quantity) / 30 as daily_velocity')
            )
            ->groupBy('products.id', 'products.name', 'products.stock')
            ->having('daily_velocity', '>', 0)
            ->get();

        $criticalProducts = $salesVelocity->filter(function ($item) {
            if ($item->daily_velocity > 0) {
                $daysUntilStockout = $item->stock / $item->daily_velocity;
                return $daysUntilStockout <= 7; // Will run out within 7 days
            }
            return false;
        });

        if ($criticalProducts->isNotEmpty()) {
            AiInsight::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'engine' => 'inventory_ai',
                    'type' => 'stockout_prediction',
                ],
                [
                    'title' => $criticalProducts->count() . ' Products May Stock Out Within 7 Days',
                    'description' => 'Based on sales velocity, these products will run out soon: ' . $criticalProducts->pluck('name')->take(5)->implode(', '),
                    'recommendation' => 'Urgently restock these items or enable backorder functionality.',
                    'confidence' => 85,
                    'status' => 'new',
                    'data' => $criticalProducts->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'stock' => $p->stock,
                        'daily_velocity' => round($p->daily_velocity, 1),
                        'days_until_stockout' => round($p->stock / $p->daily_velocity, 1),
                    ])->toArray(),
                ]
            );
        }
    }

    /**
     * Detect dead stock — products with no sales in 60+ days.
     */
    protected function detectDeadStock(Store $store): void
    {
        $deadStock = Product::where('store_id', $store->id)
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->whereNotIn('id', function ($query) use ($store) {
                $query->select('order_items.product_id')
                    ->from('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.store_id', $store->id)
                    ->where('orders.created_at', '>=', Carbon::now()->subDays(60));
            })
            ->get();

        if ($deadStock->isNotEmpty()) {
            AiInsight::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'engine' => 'inventory_ai',
                    'type' => 'dead_stock',
                ],
                [
                    'title' => $deadStock->count() . ' Products with No Sales in 60 Days',
                    'description' => 'These products have inventory but zero sales in the last 60 days: ' . $deadStock->pluck('name')->take(5)->implode(', '),
                    'recommendation' => 'Consider running a clearance sale, bundling them with popular products, or discontinuing them.',
                    'confidence' => 90,
                    'status' => 'new',
                    'data' => $deadStock->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'stock' => $p->stock])->toArray(),
                ]
            );
        }
    }
}
