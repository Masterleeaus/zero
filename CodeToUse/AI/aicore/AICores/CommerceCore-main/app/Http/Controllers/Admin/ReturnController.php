<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SaleReturn;
use App\Models\Store;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(private ReturnService $returnService) {}

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

        $returns = SaleReturn::where('store_id', $store->id)
            ->with(['order', 'items'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        // Analytics
        $pendingReturns = SaleReturn::where('store_id', $store->id)->where('status', 'pending')->count();
        $totalRefunded = SaleReturn::where('store_id', $store->id)->where('status', 'refunded')->sum('total_refund_amount');

        return view('admin.returns.index', compact('returns', 'pendingReturns', 'totalRefunded'));
    }

    public function create(Request $request)
    {
        $store = $this->resolveStore($request);
        
        // Ensure an order is provided to create a return
        if (!$request->has('order_id')) {
            return redirect()->route('admin.orders.index')->with('error', 'Please select an order to create a return.');
        }

        $order = Order::where('store_id', $store->id)
            ->with(['items.product'])
            ->findOrFail($request->order_id);

        return view('admin.returns.create', compact('order'));
    }

    public function store(Request $request)
    {
        $store = $this->resolveStore($request);

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:good,damaged,defective',
        ]);

        $order = Order::where('store_id', $store->id)->findOrFail($validated['order_id']);

        $saleReturn = $this->returnService->create($order, [
            'reason' => $validated['reason'],
            'notes' => $validated['notes'],
        ], $validated['items']);

        return redirect()->route('admin.returns.show', $saleReturn)
            ->with('success', 'Return request created successfully.');
    }

    public function show(SaleReturn $saleReturn, Request $request)
    {
        // Simple authorization check
        $store = $this->resolveStore($request);
        if ($saleReturn->store_id !== $store->id) {
            abort(403);
        }

        $saleReturn->load(['order', 'items.product']);

        return view('admin.returns.show', compact('saleReturn'));
    }

    public function approve(SaleReturn $saleReturn, Request $request)
    {
        if ($saleReturn->status !== 'pending') {
            return back()->with('error', 'Cannot approve a non-pending return.');
        }

        $this->returnService->approveAndRefund($saleReturn, $request->user()->id);

        return back()->with('success', 'Return approved and stock updated.');
    }

    public function reject(SaleReturn $saleReturn)
    {
        if ($saleReturn->status !== 'pending') {
            return back()->with('error', 'Cannot reject a non-pending return.');
        }

        $this->returnService->reject($saleReturn);

        return back()->with('success', 'Return request rejected.');
    }
}
