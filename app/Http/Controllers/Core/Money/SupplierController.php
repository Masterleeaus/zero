<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Events\Money\SupplierCreated;
use App\Http\Controllers\Core\CoreController;
use App\Models\Inventory\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * SupplierController (Finance / AP context).
 *
 * Operates on the canonical Inventory\Supplier model but exposes
 * the supplier registry under the money.suppliers.* route namespace
 * for AP workflows.
 */
class SupplierController extends CoreController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Supplier::class);

        $query = Supplier::query();

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $suppliers = $query->latest()->paginate(25)->withQueryString();

        return view('default.panel.user.money.suppliers.index', [
            'suppliers' => $suppliers,
            'filters'   => [
                'search' => $search ?? '',
                'status' => $status ?? '',
            ],
        ]);
    }

    public function show(Supplier $supplier): View
    {
        $this->authorize('view', $supplier);

        $supplier->load('purchaseOrders');

        return view('default.panel.user.money.suppliers.show', compact('supplier'));
    }

    public function create(): View
    {
        $this->authorize('create', Supplier::class);

        return view('default.panel.user.money.suppliers.form', [
            'supplier' => new Supplier(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Supplier::class);

        $data = $this->validated($request);

        $supplier = Supplier::create(array_merge($data, [
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]));

        event(new SupplierCreated($supplier));

        return redirect()->route('dashboard.money.suppliers.show', $supplier)
            ->with('success', __('Supplier created.'));
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorize('update', $supplier);

        return view('default.panel.user.money.suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('update', $supplier);

        $supplier->update($this->validated($request));

        return redirect()->route('dashboard.money.suppliers.show', $supplier)
            ->with('success', __('Supplier updated.'));
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        $supplier->delete();

        return redirect()->route('dashboard.money.suppliers.index')
            ->with('success', __('Supplier deleted.'));
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:50',
            'address'            => 'nullable|string|max:500',
            'city'               => 'nullable|string|max:100',
            'country'            => 'nullable|string|max:100',
            'tax_number'         => 'nullable|string|max:100',
            'payment_terms'      => 'nullable|string|max:100',
            'currency_code'      => 'nullable|string|max:10',
            'default_account_id' => 'nullable|integer|exists:accounts,id',
            'notes'              => 'nullable|string',
            'status'             => 'nullable|in:active,inactive',
        ]);
    }
}
