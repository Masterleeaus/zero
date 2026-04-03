<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use App\Models\Money\Account;
use App\Models\Money\SupplierBill;
use App\Services\TitanMoney\SupplierBillService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierBillController extends CoreController
{
    public function __construct(private readonly SupplierBillService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupplierBill::class);

        $query = SupplierBill::query()->with('supplier');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where('reference', 'like', '%' . $search . '%');
        }

        $bills = $query->latest('bill_date')->paginate(25)->withQueryString();

        return view('default.panel.user.money.supplier-bills.index', [
            'bills'   => $bills,
            'filters' => [
                'status' => $status ?? '',
                'search' => $search ?? '',
            ],
        ]);
    }

    public function show(SupplierBill $supplierBill): View
    {
        $this->authorize('view', $supplierBill);

        $supplierBill->load(['supplier', 'purchaseOrder', 'lines.account', 'payments']);

        return view('default.panel.user.money.supplier-bills.show', [
            'bill' => $supplierBill,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SupplierBill::class);

        $suppliers = Supplier::query()->where('status', 'active')->orderBy('name')->get();
        $accounts  = Account::query()->whereIn('type', ['expense', 'asset'])->orderBy('code')->get();
        $pos       = PurchaseOrder::query()
            ->whereIn('status', ['sent', 'partial', 'received'])
            ->orderBy('po_number')
            ->get();

        return view('default.panel.user.money.supplier-bills.form', [
            'bill'      => new SupplierBill(),
            'suppliers' => $suppliers,
            'accounts'  => $accounts,
            'pos'       => $pos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SupplierBill::class);

        $data  = $this->validatedBill($request);
        $lines = $this->validatedLines($request);

        $bill = $this->service->createBill(
            array_merge($data, [
                'company_id' => $request->user()->company_id,
                'created_by' => $request->user()->id,
            ]),
            $lines
        );

        return redirect()->route('dashboard.money.supplier-bills.show', $bill)
            ->with('success', __('Supplier bill created.'));
    }

    public function edit(SupplierBill $supplierBill): View
    {
        $this->authorize('update', $supplierBill);

        $suppliers = Supplier::query()->where('status', 'active')->orderBy('name')->get();
        $accounts  = Account::query()->whereIn('type', ['expense', 'asset'])->orderBy('code')->get();
        $pos       = PurchaseOrder::query()
            ->whereIn('status', ['sent', 'partial', 'received'])
            ->orderBy('po_number')
            ->get();

        return view('default.panel.user.money.supplier-bills.form', [
            'bill'      => $supplierBill->load('lines'),
            'suppliers' => $suppliers,
            'accounts'  => $accounts,
            'pos'       => $pos,
        ]);
    }

    public function update(Request $request, SupplierBill $supplierBill): RedirectResponse
    {
        $this->authorize('update', $supplierBill);

        $data  = $this->validatedBill($request);
        $lines = $this->validatedLines($request);

        $this->service->updateBill($supplierBill, $data, $lines);

        return redirect()->route('dashboard.money.supplier-bills.show', $supplierBill)
            ->with('success', __('Supplier bill updated.'));
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function validatedBill(Request $request): array
    {
        return $request->validate([
            'supplier_id'       => 'required|integer|exists:suppliers,id',
            'purchase_order_id' => 'nullable|integer|exists:purchase_orders,id',
            'reference'         => 'nullable|string|max:255',
            'bill_date'         => 'required|date',
            'due_date'          => 'nullable|date|after_or_equal:bill_date',
            'currency'          => 'nullable|string|max:10',
            'subtotal'          => 'nullable|numeric|min:0',
            'tax_total'         => 'nullable|numeric|min:0',
            'total'             => 'nullable|numeric|min:0',
            'status'            => 'nullable|in:draft,awaiting_payment,partial,paid,overdue,void',
            'notes'             => 'nullable|string',
        ]);
    }

    private function validatedLines(Request $request): array
    {
        if (! $request->has('lines')) {
            return [];
        }

        $request->validate([
            'lines'                  => 'array',
            'lines.*.account_id'     => 'nullable|integer|exists:accounts,id',
            'lines.*.service_job_id' => 'nullable|integer',
            'lines.*.description'    => 'nullable|string|max:500',
            'lines.*.amount'         => 'required|numeric|min:0',
            'lines.*.tax_rate'       => 'nullable|numeric|min:0|max:100',
            'lines.*.tax_amount'     => 'nullable|numeric|min:0',
        ]);

        return $request->input('lines', []);
    }
}
