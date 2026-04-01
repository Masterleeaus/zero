<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Order;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index(string $storeSlug)
    {
        $store = Store::where('slug', $storeSlug)->firstOrFail();
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $orders = $user->orders()
            ->where('store_id', $store->id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $subscriptions = $user->orders()
            ->where('store_id', $store->id)
            ->where('is_subscription', true)
            ->whereNull('parent_subscription_id')
            ->with(['items.product', 'recurringOrders'])
            ->get();

        $loyaltyHistory = $user->loyaltyPoints()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('storefront.account.index', compact('store', 'user', 'orders', 'subscriptions', 'loyaltyHistory'));
    }

    public function cancelSubscription(string $storeSlug, Order $order)
    {
        if ($order->user_id !== Auth::id() || !$order->is_subscription) {
            abort(403);
        }

        $order->update([
            'status' => 'cancelled',
            'is_subscription' => false // Stop future recurring
        ]);

        return back()->with('success', 'Subscription cancelled successfully.');
    }
}
