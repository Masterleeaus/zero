<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Events\Money\PurchaseOrderIssued;
use App\Http\Controllers\Core\CoreController;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * PurchaseOrderController (Finance / AP context).
 *
 * Operates on the canonical Inventory\PurchaseOrder model but exposes
 * PO management under the money.purchase-orders.* route namespace
 * for AP workflows. The `service_job_id` field added in migration 600200
 * enables job-costing linkage.
 */
class PurchaseOrderController extends CoreController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $query = PurchaseOrder::query()->with('supplier');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where('po_number', 'like', '%' . $search . '%');
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $purchaseOrders = $query->latest('order_date')->paginate(25)->withQueryString();

        return view('default.panel.user.money.purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'filters'        => [
                'search' => $search ?? '',
                'status' => $status ?? '',
            ],
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('view', $purchaseOrder);

        $purchaseOrder->load(['supplier', 'items']);

        return view('default.panel.user.money.purchase-orders.show', compact('purchaseOrder'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', PurchaseOrder::class);

        $suppliers = Supplier::query()->where('status', 'active')->orderBy('name')->get();

        return view('default.panel.user.money.purchase-orders.form', [
            'purchaseOrder' => new PurchaseOrder(),
            'suppliers'     => $suppliers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        $data = $this->validated($request);

        $purchaseOrder = DB::transaction(function () use ($data, $request): PurchaseOrder {
            return PurchaseOrder::create(array_merge($data, [
                'company_id' => $request->user()->company_id,
                'created_by' => $request->user()->id,
            ]));
        });

        if ($purchaseOrder->status === 'sent') {
            event(new PurchaseOrderIssued($purchaseOrder));
        }

        return redirect()->route('dashboard.money.purchase-orders.show', $purchaseOrder)
            ->with('success', __('Purchase order created.'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('update', $purchaseOrder);

        $suppliers = Supplier::query()->where('status', 'active')->orderBy('name')->get();

        return view('default.panel.user.money.purchase-orders.form', [
            'purchaseOrder' => $purchaseOrder,
            'suppliers'     => $suppliers,
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('update', $purchaseOrder);

        $wasNotIssued = ! in_array($purchaseOrder->status, ['sent', 'partial', 'received'], true);

        $purchaseOrder->update($this->validated($request));

        if ($wasNotIssued && $purchaseOrder->fresh()->status === 'sent') {
            event(new PurchaseOrderIssued($purchaseOrder->fresh()));
        }

        return redirect()->route('dashboard.money.purchase-orders.show', $purchaseOrder)
            ->with('success', __('Purchase order updated.'));
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('delete', $purchaseOrder);

        $purchaseOrder->delete();

        return redirect()->route('dashboard.money.purchase-orders.index')
            ->with('success', __('Purchase order deleted.'));
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function validated(Request $request): array
    {
        return $request->validate([
            'po_number'       => 'required|string|max:100',
            'supplier_id'     => 'required|integer|exists:suppliers,id',
            'status'          => 'nullable|in:draft,sent,partial,received,cancelled',
            'order_date'      => 'nullable|date',
            'expected_date'   => 'nullable|date',
            'reference'       => 'nullable|string|max:255',
            'currency_code'   => 'nullable|string|max:10',
            'service_job_id'  => 'nullable|integer',
            'notes'           => 'nullable|string',
            'subtotal'        => 'nullable|numeric|min:0',
            'tax_amount'      => 'nullable|numeric|min:0',
            'total_amount'    => 'nullable|numeric|min:0',
        ]);
    }
}
