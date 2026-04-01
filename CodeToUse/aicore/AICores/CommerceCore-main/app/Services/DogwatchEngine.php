<?php

namespace App\Services;

use App\Models\SystemAlert;
use App\Models\Store;
use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DogwatchEngine
{
    /**
     * Run all system health checks for every active store.
     */
    public function runAll(): void
    {
        Store::where('status', 'active')->chunk(50, function ($stores) {
            foreach ($stores as $store) {
                $this->checkStore($store);
            }
        });
    }

    public function checkStore(Store $store): void
    {
        $this->detectPaymentFailures($store);
        $this->detectAbnormalStockDrops($store);
        $this->detectRevenueAnomaly($store);
        $this->detectExpiredSubscriptions($store);
        $this->detectHighCartAbandonment($store);
    }

    /**
     * Detect recent payment failures.
     */
    protected function detectPaymentFailures(Store $store): void
    {
        $failedPayments = Payment::where('store_id', $store->id)
            ->where('status', 'failed')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->count();

        if ($failedPayments >= 3) {
            SystemAlert::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'type' => 'payment_failure',
                    'status' => 'active',
                ],
                [
                    'severity' => $failedPayments >= 10 ? 'critical' : 'warning',
                    'title' => 'Multiple Payment Failures Detected',
                    'message' => "{$failedPayments} payment(s) have failed in the last 24 hours. Your payment gateway may be experiencing issues.",
                    'suggested_action' => 'Check your SSLCommerz dashboard for gateway status. Verify API credentials in .env are correct.',
                    'metadata' => ['failed_count' => $failedPayments, 'period' => '24h'],
                ]
            );
        }
    }

    /**
     * Detect abnormal stock drops (>50% drop in a single day for any product).
     */
    protected function detectAbnormalStockDrops(Store $store): void
    {
        $suspiciousProducts = DB::table('stock_movements')
            ->where('store_id', $store->id)
            ->where('type', 'out')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->select('product_id', DB::raw('SUM(quantity) as total_out'))
            ->groupBy('product_id')
            ->get()
            ->filter(function ($movement) {
                $product = Product::find($movement->product_id);
                if ($product && $product->stock > 0) {
                    $originalStock = $product->stock + $movement->total_out;
                    return ($movement->total_out / $originalStock) > 0.5;
                }
                return false;
            });

        if ($suspiciousProducts->isNotEmpty()) {
            SystemAlert::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'type' => 'abnormal_stock_drop',
                    'status' => 'active',
                ],
                [
                    'severity' => 'warning',
                    'title' => 'Abnormal Stock Reduction Detected',
                    'message' => $suspiciousProducts->count() . ' product(s) had more than 50% of their stock removed in 24 hours. This could indicate a data error or theft.',
                    'suggested_action' => 'Review stock movement logs and verify with warehouse staff.',
                    'metadata' => ['product_ids' => $suspiciousProducts->pluck('product_id')->toArray()],
                ]
            );
        }
    }

    /**
     * Detect sudden revenue anomaly (zero orders for >12 hours during business hours).
     */
    protected function detectRevenueAnomaly(Store $store): void
    {
        $lastOrder = Order::where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->first();

        if ($lastOrder && $lastOrder->created_at->diffInHours(now()) >= 12) {
            $hoursSince = $lastOrder->created_at->diffInHours(now());

            SystemAlert::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'type' => 'revenue_anomaly',
                    'status' => 'active',
                ],
                [
                    'severity' => $hoursSince >= 24 ? 'critical' : 'warning',
                    'title' => 'No Orders in ' . $hoursSince . ' Hours',
                    'message' => "Your store hasn't received any orders in {$hoursSince} hours. This is unusual and may indicate a website issue, payment gateway problem, or traffic drop.",
                    'suggested_action' => 'Check your storefront is accessible. Verify payment gateway. Review traffic analytics.',
                    'metadata' => ['hours_since_last_order' => $hoursSince],
                ]
            );
        }
    }

    /**
     * Detect expired or expiring subscriptions.
     */
    protected function detectExpiredSubscriptions(Store $store): void
    {
        $activeSubscription = $store->activeSubscription();

        if (!$activeSubscription) {
            SystemAlert::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'type' => 'subscription_expired',
                    'status' => 'active',
                ],
                [
                    'severity' => 'critical',
                    'title' => 'No Active Subscription',
                    'message' => 'Your store does not have an active subscription. Some features may be restricted.',
                    'suggested_action' => 'Go to Billing and subscribe to a plan to restore full functionality.',
                ]
            );
        } elseif ($activeSubscription->isTrialing() && $activeSubscription->trial_ends_at->diffInDays(now()) <= 3) {
            SystemAlert::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'type' => 'trial_expiring',
                    'status' => 'active',
                ],
                [
                    'severity' => 'warning',
                    'title' => 'Trial Expiring Soon',
                    'message' => 'Your free trial expires in ' . $activeSubscription->trial_ends_at->diffInDays(now()) . ' day(s). Subscribe to keep your store running.',
                    'suggested_action' => 'Choose a plan and complete payment before your trial ends.',
                ]
            );
        }
    }

    /**
     * Detect high cart abandonment rates.
     */
    protected function detectHighCartAbandonment(Store $store): void
    {
        $abandonedCarts = DB::table('abandoned_carts')
            ->where('store_id', $store->id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $completedOrders = Order::where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $total = $abandonedCarts + $completedOrders;
        if ($total > 0 && $abandonedCarts > 0) {
            $abandonRate = ($abandonedCarts / $total) * 100;

            if ($abandonRate >= 70) {
                SystemAlert::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'type' => 'high_cart_abandonment',
                        'status' => 'active',
                    ],
                    [
                        'severity' => 'warning',
                        'title' => 'High Cart Abandonment Rate (' . round($abandonRate) . '%)',
                        'message' => sprintf('%d out of %d shopping sessions were abandoned in the last 7 days.', $abandonedCarts, $total),
                        'suggested_action' => 'Simplify checkout, add trust badges, offer free shipping, or send abandoned cart recovery emails.',
                        'metadata' => ['abandon_rate' => round($abandonRate, 1), 'abandoned' => $abandonedCarts, 'completed' => $completedOrders],
                    ]
                );
            }
        }
    }
}
