<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $warehouses = Warehouse::query()
            ->when($request->q, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($warehouses);
        }

        return view('default.panel.user.inventory.warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('default.panel.user.inventory.warehouses.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'code'       => ['nullable', 'string', 'max:50'],
            'address'    => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'status'     => ['nullable', 'in:active,inactive'],
        ]);

        $warehouse = Warehouse::create($data);

        if ($request->expectsJson()) {
            return response()->json($warehouse, 201);
        }

        return redirect()->route('dashboard.inventory.warehouses.show', $warehouse)
            ->with('success', __('Warehouse created.'));
    }

    public function show(Warehouse $warehouse): View|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json($warehouse);
        }

        return view('default.panel.user.inventory.warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('default.panel.user.inventory.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'code'       => ['nullable', 'string', 'max:50'],
            'address'    => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'status'     => ['nullable', 'in:active,inactive'],
        ]);

        $warehouse->update($data);

        if ($request->expectsJson()) {
            return response()->json($warehouse->fresh());
        }

        return redirect()->route('dashboard.inventory.warehouses.show', $warehouse)
            ->with('success', __('Warehouse updated.'));
    }

    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse|JsonResponse
    {
        $warehouse->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Deleted.']);
        }

        return redirect()->route('dashboard.inventory.warehouses.index')
            ->with('success', __('Warehouse deleted.'));
    }
}
