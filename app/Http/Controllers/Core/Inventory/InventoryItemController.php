<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $items = InventoryItem::query()
            ->when($request->q, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($items);
        }

        return view('default.panel.user.inventory.items.index', compact('items'));
    }

    public function create(): View
    {
        return view('default.panel.user.inventory.items.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'sku'            => ['nullable', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'category'       => ['nullable', 'string', 'max:100'],
            'unit_price'     => ['nullable', 'numeric', 'min:0'],
            'cost_price'     => ['nullable', 'numeric', 'min:0'],
            'reorder_point'  => ['nullable', 'integer', 'min:0'],
            'unit'           => ['nullable', 'string', 'max:50'],
            'track_quantity' => ['nullable', 'boolean'],
            'status'         => ['nullable', 'in:active,inactive'],
        ]);

        $item = InventoryItem::create($data);

        if ($request->expectsJson()) {
            return response()->json($item, 201);
        }

        return redirect()->route('dashboard.inventory.items.show', $item)
            ->with('success', __('Item created.'));
    }

    public function show(InventoryItem $item): View|JsonResponse
    {
        $item->load('stockMovements');

        if (request()->expectsJson()) {
            return response()->json($item);
        }

        return view('default.panel.user.inventory.items.show', compact('item'));
    }

    public function edit(InventoryItem $item): View
    {
        return view('default.panel.user.inventory.items.edit', compact('item'));
    }

    public function update(Request $request, InventoryItem $item): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'sku'            => ['nullable', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'category'       => ['nullable', 'string', 'max:100'],
            'unit_price'     => ['nullable', 'numeric', 'min:0'],
            'cost_price'     => ['nullable', 'numeric', 'min:0'],
            'reorder_point'  => ['nullable', 'integer', 'min:0'],
            'unit'           => ['nullable', 'string', 'max:50'],
            'track_quantity' => ['nullable', 'boolean'],
            'status'         => ['nullable', 'in:active,inactive'],
        ]);

        $item->update($data);

        if ($request->expectsJson()) {
            return response()->json($item->fresh());
        }

        return redirect()->route('dashboard.inventory.items.show', $item)
            ->with('success', __('Item updated.'));
    }

    public function destroy(Request $request, InventoryItem $item): RedirectResponse|JsonResponse
    {
        $item->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Deleted.']);
        }

        return redirect()->route('dashboard.inventory.items.index')
            ->with('success', __('Item deleted.'));
    }
}
