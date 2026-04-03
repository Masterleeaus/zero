<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ResolvesStore;

    public function index(Request $request)
    {
        $store   = $this->getActiveStore($request);
        $storeId = $store->id;

        $suppliers = Supplier::where('store_id', $storeId)
            ->withCount('purchases')
            ->withSum('purchases', 'total_amount')
            ->latest()
            ->paginate(20);

        $totalSuppliers = Supplier::where('store_id', $storeId)->count();
        $activeSuppliers = Supplier::where('store_id', $storeId)->where('status', 'active')->count();
        $totalSpend = Supplier::where('store_id', $storeId)
            ->withSum('purchases', 'total_amount')
            ->get()
            ->sum('purchases_sum_total_amount') ?? 0;

        return view('admin.suppliers.index', compact('suppliers', 'totalSuppliers', 'activeSuppliers', 'totalSpend'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $store = $this->getActiveStore($request);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'status'  => 'required|in:active,inactive',
        ]);

        $validated['store_id'] = $store->id;
        Supplier::create($validated);

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created successfully!');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $store = $this->getActiveStore($request);
        if ($supplier->store_id !== $store->id && !$request->user()->isSuperAdmin()) {
            abort(403, 'This supplier does not belong to your store.');
        }

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'status'  => 'required|in:active,inactive',
        ]);

        $supplier->update($validated);

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated!');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        $store = $this->getActiveStore($request);
        if ($supplier->store_id !== $store->id && !$request->user()->isSuperAdmin()) {
            abort(403, 'This supplier does not belong to your store.');
        }

        $supplier->delete();
        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted!');
    }
}
