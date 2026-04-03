<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $movements = StockMovement::with(['item', 'warehouse'])
            ->when($request->item_id, fn ($q, $id) => $q->where('item_id', $id))
            ->when($request->warehouse_id, fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate(30);

        if ($request->expectsJson()) {
            return response()->json($movements);
        }

        return view('default.panel.user.inventory.stock-movements.index', compact('movements'));
    }
}
