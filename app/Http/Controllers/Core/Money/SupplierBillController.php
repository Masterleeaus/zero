<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Inventory\Supplier;
use App\Models\Money\Account;
use App\Models\Money\SupplierBill;
use App\Services\TitanMoney\SupplierBillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierBillController extends CoreController
{
    public function __construct(private readonly SupplierBillService $service) {}

    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $bills = SupplierBill::where('company_id', $companyId)
            ->with('supplier')
            ->latest('bill_date')
            ->paginate(25)
            ->withQueryString();

        return view('default.panel.user.money.supplier-bills.index', compact('bills'));
    }

    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;

        return view('default.panel.user.money.supplier-bills.create', [
            'bill'      => new SupplierBill(),
            'suppliers' => Supplier::where('company_id', $companyId)->orderBy('name')->get(),
            'accounts'  => Account::where('company_id', $companyId)->active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'supplier_id'       => 'required|integer',
            'bill_date'         => 'required|date',
            'due_date'          => 'required|date|after_or_equal:bill_date',
            'reference'         => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
            'purchase_order_id' => 'nullable|integer',
            'items'             => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.account_id'  => 'nullable|integer',
        ]);

        $bill = $this->service->create(array_merge($validated, [
            'company_id' => $companyId,
            'created_by' => $request->user()->id,
        ]));

        return redirect()
            ->route('dashboard.money.supplier-bills.show', $bill)
            ->with('success', __('Supplier bill created.'));
    }

    public function show(Request $request, SupplierBill $supplierBill): View
    {
        abort_if($supplierBill->company_id !== $request->user()->company_id, 403);

        $supplierBill->load(['supplier', 'items.account', 'purchaseOrder', 'approver']);

        return view('default.panel.user.money.supplier-bills.show', [
            'bill' => $supplierBill,
        ]);
    }

    public function approve(Request $request, SupplierBill $supplierBill): RedirectResponse
    {
        abort_if($supplierBill->company_id !== $request->user()->company_id, 403);

        $this->service->approve($supplierBill, $request->user()->id);

        return back()->with('success', __('Supplier bill approved.'));
    }

    public function recordPayment(Request $request, SupplierBill $supplierBill): RedirectResponse
    {
        abort_if($supplierBill->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $this->service->recordPayment($supplierBill, (float) $validated['amount']);

        return back()->with('success', __('Payment recorded against supplier bill.'));
    }
}
