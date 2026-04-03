<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Services\OrderService;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ResolvesStore;

    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $store = $this->getActiveStore($request);

        $orders = $this->orderService->getForStore($store, [
            'status' => $request->status,
            'search' => $request->search,
        ]);

        $orderTrends = Order::where('store_id', $store->id)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_price) as revenue')
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $statusDistribution = Order::where('store_id', $store->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.orders.index', compact('orders', 'store', 'orderTrends', 'statusDistribution'));
    }

    public function show(Request $request, Order $order)
    {
        // ✅ Authorization: ensure this order belongs to the active store
        if (!$request->user()->isSuperAdmin()) {
            $store = $this->getActiveStore($request);
            if ($order->store_id !== $store->id) {
                abort(403, 'You do not have access to this order.');
            }
        }

        $order->load('items.product', 'store', 'shipment.courier');
        $couriers = \App\Models\Courier::where('is_active', true)->get();
        return view('admin.orders.show', compact('order', 'couriers'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        // ✅ Authorization: ensure this order belongs to the active store
        if (!$request->user()->isSuperAdmin()) {
            $store = $this->getActiveStore($request);
            if ($order->store_id !== $store->id) {
                abort(403, 'You do not have access to this order.');
            }
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,paid,shipped,delivered,cancelled',
        ]);

        $this->orderService->updateStatus($order, $validated['status']);

        return redirect()->back()->with('success', 'Order status updated!');
    }
}
