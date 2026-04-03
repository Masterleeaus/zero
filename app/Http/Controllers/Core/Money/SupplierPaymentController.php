<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\Account;
use App\Models\Money\SupplierBill;
use App\Models\Money\SupplierPayment;
use App\Services\TitanMoney\SupplierBillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierPaymentController extends CoreController
{
    public function __construct(private readonly SupplierBillService $service) {}

    /**
     * Record a payment against a supplier bill.
     */
    public function store(Request $request, SupplierBill $supplierBill): RedirectResponse
    {
        $this->authorize('recordPayment', $supplierBill);

        $data = $request->validate([
            'amount'             => 'required|numeric|min:0.01',
            'payment_date'       => 'required|date',
            'payment_account_id' => 'nullable|integer|exists:accounts,id',
            'reference'          => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
        ]);

        $this->service->recordPayment($supplierBill, array_merge($data, [
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]));

        return redirect()->route('dashboard.money.supplier-bills.show', $supplierBill)
            ->with('success', __('Payment recorded.'));
    }
}
