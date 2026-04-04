<?php

namespace App\Services\AI;

use App\Models\AiInsight;
use App\Models\Order;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesAIEngine
{
    /**
     * Analyze sales performance for a store and detect revenue drops.
     * Run via scheduled job: $schedule->call(fn() => app(SalesAIEngine::class)->analyzeAll())->daily();
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
        $this->detectRevenueDrop($store);
        $this->detectTopProducts($store);
        $this->detectSlowDays($store);
    }

    /**
     * Detect >20% week-over-week revenue drops.
     */
    protected function detectRevenueDrop(Store $store): void
    {
        $thisWeekRevenue = Order::where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()])
            ->sum('total_price');

        $lastWeekRevenue = Order::where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->sum('total_price');

        if ($lastWeekRevenue > 0) {
            $changePercent = (($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100;

            if ($changePercent <= -20) {
                AiInsight::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'engine' => 'sales_ai',
                        'type' => 'revenue_drop',
                        'status' => 'new',
                    ],
                    [
                        'title' => 'Revenue Drop Detected',
                        'description' => sprintf(
                            'Revenue dropped by %.1f%% this week (৳%s) compared to last week (৳%s).',
                            abs($changePercent),
                            number_format($thisWeekRevenue, 2),
                            number_format($lastWeekRevenue, 2)
                        ),
                        'recommendation' => 'Consider running a flash sale, sending promotional SMS/emails, or reviewing recent customer complaints.',
                        'confidence' => min(abs($changePercent), 100),
                        'data' => [
                            'this_week' => $thisWeekRevenue,
                            'last_week' => $lastWeekRevenue,
                            'change_percent' => round($changePercent, 1),
                        ],
                    ]
                );
            }
        }
    }

    /**
     * Identify top selling products this month.
     */
    protected function detectTopProducts(Store $store): void
    {
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.store_id', $store->id)
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', Carbon::now()->startOfMonth())
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'), DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'))
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        if ($topProducts->isNotEmpty()) {
            AiInsight::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'engine' => 'sales_ai',
                    'type' => 'top_products',
                ],
                [
                    'title' => 'Top Selling Products This Month',
                    'description' => 'Your best performers are: ' . $topProducts->pluck('name')->implode(', '),
                    'recommendation' => 'Consider increasing stock for these items and featuring them prominently.',
                    'confidence' => 95,
                    'status' => 'new',
                    'data' => $topProducts->toArray(),
                ]
            );
        }
    }

    /**
     * Detect slow selling days of the week.
     */
    protected function detectSlowDays(Store $store): void
    {
        $dailySales = DB::table('orders')
            ->where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('DAYNAME(created_at) as day_name'), DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total_price) as revenue'))
            ->groupBy('day_name')
            ->orderBy('order_count')
            ->get();

        if ($dailySales->isNotEmpty() && $dailySales->first()->order_count < $dailySales->avg('order_count') * 0.5) {
            $slowDay = $dailySales->first();
            AiInsight::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'engine' => 'sales_ai',
                    'type' => 'slow_day',
                ],
                [
                    'title' => "Slow Sales on {$slowDay->day_name}s",
                    'description' => sprintf('%s typically has only %d orders. Consider running promotions on this day.', $slowDay->day_name, $slowDay->order_count),
                    'recommendation' => "Run a '{$slowDay->day_name} Special' discount or free delivery promotion.",
                    'confidence' => 80,
                    'status' => 'new',
                    'data' => $dailySales->toArray(),
                ]
            );
        }
    }
}
