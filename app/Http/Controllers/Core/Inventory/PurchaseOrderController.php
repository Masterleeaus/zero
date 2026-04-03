<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use App\Services\Inventory\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $poService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $orders = PurchaseOrder::with('supplier')
            ->when($request->q, fn ($q, $search) => $q->where('po_number', 'like', "%{$search}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($orders);
        }

        return view('default.panel.user.inventory.purchase-orders.index', compact('orders'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('status', 'active')->get();

        return view('default.panel.user.inventory.purchase-orders.create', compact('suppliers'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'supplier_id'         => ['required', 'integer'],
            'order_date'          => ['nullable', 'date'],
            'expected_date'       => ['nullable', 'date'],
            'reference'           => ['nullable', 'string', 'max:255'],
            'notes'               => ['nullable', 'string'],
            'currency_code'       => ['nullable', 'string', 'max:10'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.item_id'     => ['nullable', 'integer'],
            'items.*.qty_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $po = $this->poService->createPurchaseOrder($data, $data['items']);

        if ($request->expectsJson()) {
            return response()->json($po, 201);
        }

        return redirect()->route('dashboard.inventory.purchase-orders.show', $po)
            ->with('success', __('Purchase order created.'));
    }

    public function show(PurchaseOrder $purchaseOrder): View|JsonResponse
    {
        $purchaseOrder->load(['supplier', 'items.inventoryItem']);

        if (request()->expectsJson()) {
            return response()->json($purchaseOrder);
        }

        return view('default.panel.user.inventory.purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        $suppliers = Supplier::where('status', 'active')->get();

        return view('default.panel.user.inventory.purchase-orders.edit', compact('purchaseOrder', 'suppliers'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'supplier_id'   => ['required', 'integer'],
            'order_date'    => ['nullable', 'date'],
            'expected_date' => ['nullable', 'date'],
            'reference'     => ['nullable', 'string', 'max:255'],
            'notes'         => ['nullable', 'string'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'status'        => ['nullable', 'in:draft,sent,partial,received,cancelled'],
        ]);

        $purchaseOrder->update($data);

        if ($request->expectsJson()) {
            return response()->json($purchaseOrder->fresh());
        }

        return redirect()->route('dashboard.inventory.purchase-orders.show', $purchaseOrder)
            ->with('success', __('Purchase order updated.'));
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse|JsonResponse
    {
        $purchaseOrder->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Deleted.']);
        }

        return redirect()->route('dashboard.inventory.purchase-orders.index')
            ->with('success', __('Purchase order deleted.'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'items'                 => ['required', 'array'],
            'items.*.id'            => ['required', 'integer'],
            'items.*.qty_receiving' => ['required', 'integer', 'min:1'],
            'items.*.warehouse_id'  => ['required', 'integer'],
        ]);

        $this->poService->receivePurchaseOrder($purchaseOrder, $data['items']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Items received.']);
        }

        return redirect()->route('dashboard.inventory.purchase-orders.show', $purchaseOrder)
            ->with('success', __('Items received and stock updated.'));
    }
}
