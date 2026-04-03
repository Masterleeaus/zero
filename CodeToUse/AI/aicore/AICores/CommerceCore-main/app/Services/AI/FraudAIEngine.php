<?php

namespace App\Services\AI;

use App\Models\AiInsight;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FraudAIEngine
{
    /**
     * Analyze all stores for fraud patterns and VIP customers.
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
        $this->detectVIPCustomers($store);
        $this->detectSuspiciousOrders($store);
        $this->detectHighCancellationRate($store);
    }

    /**
     * Segment VIP customers (top 10% by lifetime value).
     */
    protected function detectVIPCustomers(Store $store): void
    {
        $topCustomers = DB::table('orders')
            ->where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('user_id')
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_price) as lifetime_value')
            )
            ->groupBy('user_id')
            ->orderByDesc('lifetime_value')
            ->limit(10)
            ->get();

        if ($topCustomers->isNotEmpty()) {
            $userNames = User::whereIn('id', $topCustomers->pluck('user_id'))->pluck('name', 'id');

            AiInsight::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'engine' => 'fraud_ai',
                    'type' => 'vip_segment',
                ],
                [
                    'title' => 'Top VIP Customers Identified',
                    'description' => 'Your most valuable customers by lifetime spend: ' . $userNames->values()->take(5)->implode(', '),
                    'recommendation' => 'Send them exclusive offers, loyalty rewards, or early access to new products.',
                    'confidence' => 95,
                    'status' => 'new',
                    'data' => $topCustomers->map(fn($c) => [
                        'user_id' => $c->user_id,
                        'name' => $userNames[$c->user_id] ?? 'Guest',
                        'total_orders' => $c->total_orders,
                        'lifetime_value' => round($c->lifetime_value, 2),
                    ])->toArray(),
                ]
            );
        }
    }

    /**
     * Detect suspicious orders — unusually large orders or repeated failed payments.
     */
    protected function detectSuspiciousOrders(Store $store): void
    {
        $avgOrderValue = Order::where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->avg('total_price');

        if (!$avgOrderValue) return;

        $suspiciousOrders = Order::where('store_id', $store->id)
            ->where('total_price', '>', $avgOrderValue * 5) // 5x above average
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->get();

        if ($suspiciousOrders->isNotEmpty()) {
            AiInsight::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'engine' => 'fraud_ai',
                    'type' => 'suspicious_orders',
                ],
                [
                    'title' => $suspiciousOrders->count() . ' Suspicious Orders Detected',
                    'description' => sprintf(
                        'Orders with values 5x above your average (৳%s) were found in the last 7 days.',
                        number_format($avgOrderValue, 2)
                    ),
                    'recommendation' => 'Manually review these orders before shipping. Consider enabling phone verification.',
                    'confidence' => 70,
                    'status' => 'new',
                    'data' => $suspiciousOrders->map(fn($o) => [
                        'order_id' => $o->id,
                        'order_number' => $o->order_number,
                        'total' => $o->total_price,
                        'customer' => $o->customer_name,
                    ])->toArray(),
                ]
            );
        }
    }

    /**
     * Detect high cancellation rates from specific customers or phone numbers.
     */
    protected function detectHighCancellationRate(Store $store): void
    {
        $cancellers = DB::table('orders')
            ->where('store_id', $store->id)
            ->where('status', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select('phone', DB::raw('COUNT(*) as cancel_count'))
            ->groupBy('phone')
            ->having('cancel_count', '>=', 3)
            ->get();

        if ($cancellers->isNotEmpty()) {
            AiInsight::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'engine' => 'fraud_ai',
                    'type' => 'high_cancellation',
                ],
                [
                    'title' => 'High Cancellation Rate Detected',
                    'description' => $cancellers->count() . ' phone number(s) have 3+ cancelled orders in the last 30 days.',
                    'recommendation' => 'Consider blocking these numbers or requiring advance payment for repeat offenders.',
                    'confidence' => 85,
                    'status' => 'new',
                    'data' => $cancellers->toArray(),
                ]
            );
        }
    }
}
