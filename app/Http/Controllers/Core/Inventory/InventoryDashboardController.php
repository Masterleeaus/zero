<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryAudit;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'supplier_count'  => Supplier::count(),
            'item_count'      => InventoryItem::count(),
            'warehouse_count' => Warehouse::count(),
            'low_stock_count' => InventoryItem::whereColumn('qty_on_hand', '<=', 'reorder_point')
                ->where('track_quantity', true)
                ->count(),
        ];

        $recentMovements = StockMovement::with(['item', 'warehouse'])
            ->latest()
            ->limit(10)
            ->get();

        return view('default.panel.user.inventory.index', compact('stats', 'recentMovements'));
    }

    public function audit(Request $request): View
    {
        $audits = InventoryAudit::with('creator')
            ->latest()
            ->paginate(30);

        return view('default.panel.user.inventory.audit', compact('audits'));
    }
}
