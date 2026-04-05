<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\MaterialUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockIssueController extends Controller
{
    public function __construct(private readonly MaterialUsageService $usageService) {}

    public function create(): View
    {
        $items      = InventoryItem::where('status', 'active')->get();
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('default.panel.user.inventory.stock-issue.create', compact('items', 'warehouses'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'item_id'        => ['required', 'integer'],
            'warehouse_id'   => ['required', 'integer'],
            'service_job_id' => ['required', 'integer'],
            'qty'            => ['required', 'integer', 'min:1'],
            'cost_per_unit'  => ['nullable', 'numeric', 'min:0'],
            'note'           => ['nullable', 'string', 'max:500'],
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();

        $movement = $this->usageService->issueToJob($data);

        if ($request->expectsJson()) {
            return response()->json($movement, 201);
        }

        return redirect()->route('dashboard.inventory.stock-movements.index')
            ->with('success', __('Material issued to job successfully.'));
    }
}
