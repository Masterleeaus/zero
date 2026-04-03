<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Store;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private function resolveStore(Request $request): Store
    {
        $user = $request->user();
        if ($user->isSuperAdmin()) {
            $storeId = $request->input('store_id', session('admin_store_id'));
            return $storeId ? Store::findOrFail($storeId) : Store::firstOrFail();
        }
        return $request->get('admin_store') ?? $user->ownedStores()->firstOrFail();
    }

    public function index(Request $request)
    {
        $store = $this->resolveStore($request);

        $customers = User::where('store_id', $store->id)
            ->where('role', 'customer')
            ->orderByDesc('total_spent')
            ->paginate(20);

        // Analytics
        $totalCustomers = User::where('store_id', $store->id)->where('role', 'customer')->count();
        $totalRevenue = User::where('store_id', $store->id)->where('role', 'customer')->sum('total_spent');
        $averageOrderValue = $totalCustomers > 0 ? User::where('store_id', $store->id)->where('role', 'customer')->average('total_spent') : 0;

        return view('admin.customers.index', compact('customers', 'store', 'totalCustomers', 'totalRevenue', 'averageOrderValue'));
    }

    public function show(User $user, Request $request)
    {
        $store = $this->resolveStore($request);

        // Basic authorization
        if ($user->store_id !== $store->id || $user->role !== 'customer') {
            abort(403, 'Unauthorized access to this customer.');
        }

        // Load the customer's recent orders for this store
        $orders = Order::where('store_id', $store->id)
            ->where('user_id', $user->id)
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('admin.customers.show', compact('user', 'orders'));
    }
}
