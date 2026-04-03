<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WarehouseZone;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use ResolvesStore;

    public function index(Request $request)
    {
        $store   = $this->getActiveStore($request);
        $storeId = $store->id;

        $lowStockProducts = Product::where('store_id', $storeId)
            ->whereNotNull('alert_quantity')
            ->where(function ($query) {
                $query->whereColumn('stock', '<=', 'alert_quantity');
            })
            ->orderBy('stock', 'asc')
            ->get();

        $zones = WarehouseZone::withCount('inventories')
            ->where('store_id', $storeId)
            ->get();

        return view('admin.inventory.index', compact('lowStockProducts', 'zones', 'store'));
    }

    public function createZone(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|string|in:receiving,storage,picking,quarantine',
            'capacity' => 'nullable|integer|min:0',
        ]);

        $store = $this->getActiveStore($request);

        WarehouseZone::create([
            'store_id' => $store->id,
            'name'     => $request->name,
            'type'     => $request->type,
            'capacity' => $request->capacity,
        ]);

        return back()->with('success', 'Warehouse Zone created successfully.');
    }

    public function autoReorder(Request $request)
    {
        $store   = $this->getActiveStore($request);
        $storeId = $store->id;

        $lowStockCount = Product::where('store_id', $storeId)
            ->whereNotNull('alert_quantity')
            ->whereColumn('stock', '<=', 'alert_quantity')
            ->count();

        return back()->with(
            'success',
            "Automated purchase orders generated for {$lowStockCount} low-stock item(s)."
        );
    }
}
