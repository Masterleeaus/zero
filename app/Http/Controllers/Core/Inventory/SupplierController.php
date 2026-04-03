<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Supplier;
use App\Services\Inventory\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $supplierService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $suppliers = Supplier::query()
            ->when($request->q, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($suppliers);
        }

        return view('default.panel.user.inventory.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('default.panel.user.inventory.suppliers.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['nullable', 'email', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'address'       => ['nullable', 'string'],
            'city'          => ['nullable', 'string', 'max:100'],
            'country'       => ['nullable', 'string', 'max:100'],
            'tax_number'    => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'notes'         => ['nullable', 'string'],
            'status'        => ['nullable', 'in:active,inactive'],
        ]);

        $supplier = $this->supplierService->createSupplier($data);

        if ($request->expectsJson()) {
            return response()->json($supplier, 201);
        }

        return redirect()->route('dashboard.inventory.suppliers.show', $supplier)
            ->with('success', __('Supplier created.'));
    }

    public function show(Supplier $supplier): View|JsonResponse
    {
        $supplier->load('purchaseOrders');

        if (request()->expectsJson()) {
            return response()->json($supplier);
        }

        return view('default.panel.user.inventory.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('default.panel.user.inventory.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['nullable', 'email', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'address'       => ['nullable', 'string'],
            'city'          => ['nullable', 'string', 'max:100'],
            'country'       => ['nullable', 'string', 'max:100'],
            'tax_number'    => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'notes'         => ['nullable', 'string'],
            'status'        => ['nullable', 'in:active,inactive'],
        ]);

        $supplier = $this->supplierService->updateSupplier($supplier, $data);

        if ($request->expectsJson()) {
            return response()->json($supplier);
        }

        return redirect()->route('dashboard.inventory.suppliers.show', $supplier)
            ->with('success', __('Supplier updated.'));
    }

    public function destroy(Request $request, Supplier $supplier): RedirectResponse|JsonResponse
    {
        $supplier->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Deleted.']);
        }

        return redirect()->route('dashboard.inventory.suppliers.index')
            ->with('success', __('Supplier deleted.'));
    }
}
