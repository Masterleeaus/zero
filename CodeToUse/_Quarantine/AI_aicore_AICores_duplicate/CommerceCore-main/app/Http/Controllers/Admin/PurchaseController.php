<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Store;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $purchaseService) {}

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
        $purchases = Purchase::where('store_id', $store->id)
            ->withCount('items')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        // Analytics
        $totalSpend = Purchase::where('store_id', $store->id)->where('status', '!=', 'cancelled')->sum('total_amount');
        $pendingCount = Purchase::where('store_id', $store->id)->where('status', 'pending')->count();
        $receivedCount = Purchase::where('store_id', $store->id)->where('status', 'received')->count();
        $monthlySpend = Purchase::where('store_id', $store->id)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.purchases.index', compact(
            'purchases', 'store', 'totalSpend', 'pendingCount', 'receivedCount', 'monthlySpend'
        ));
    }

    public function create(Request $request)
    {
        $store = $this->resolveStore($request);
        $products = Product::where('store_id', $store->id)->where('status', 'active')->get();
        $suppliers = \App\Models\Supplier::where('store_id', $store->id)->where('status', 'active')->get();
        return view('admin.purchases.create', compact('store', 'products', 'suppliers'));
    }

    public function store(Request $request)
    {
        $store = $this->resolveStore($request);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_status' => 'required|in:unpaid,partial,paid',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0.01',
        ]);

        $this->purchaseService->create($store->id, $validated, $validated['items']);

        return redirect()->route('admin.purchases.index')
            ->with('success', 'Purchase Order created successfully!');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('items.product', 'store');
        return view('admin.purchases.show', compact('purchase'));
    }

    public function receive(Purchase $purchase, Request $request)
    {
        if ($purchase->status !== 'pending' && $purchase->status !== 'ordered') {
            return back()->with('error', 'This purchase cannot be received.');
        }

        $this->purchaseService->receive($purchase, $request->user()->id);

        return back()->with('success', 'Purchase received! Stock has been updated.');
    }

    public function cancel(Purchase $purchase)
    {
        if ($purchase->status === 'received') {
            return back()->with('error', 'Cannot cancel a received purchase.');
        }

        $this->purchaseService->cancel($purchase);

        return back()->with('success', 'Purchase order cancelled.');
    }
}
