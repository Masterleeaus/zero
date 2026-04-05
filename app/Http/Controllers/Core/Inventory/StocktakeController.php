<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Stocktake;
use App\Models\Inventory\StocktakeLine;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StocktakeController extends Controller
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $stocktakes = Stocktake::with('warehouse')
            ->latest()
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($stocktakes);
        }

        return view('default.panel.user.inventory.stocktakes.index', compact('stocktakes'));
    }

    public function create(): View
    {
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('default.panel.user.inventory.stocktakes.create', compact('warehouses'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'integer'],
            'ref'          => ['nullable', 'string', 'max:100'],
            'notes'        => ['nullable', 'string'],
        ]);

        $stocktake = Stocktake::create($data);

        if ($request->expectsJson()) {
            return response()->json($stocktake, 201);
        }

        return redirect()->route('dashboard.inventory.stocktakes.show', $stocktake)
            ->with('success', __('Stocktake created.'));
    }

    public function show(Stocktake $stocktake): View|JsonResponse
    {
        $stocktake->load(['warehouse', 'lines.item']);

        if (request()->expectsJson()) {
            return response()->json($stocktake);
        }

        return view('default.panel.user.inventory.stocktakes.show', compact('stocktake'));
    }

    public function edit(Stocktake $stocktake): View
    {
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('default.panel.user.inventory.stocktakes.edit', compact('stocktake', 'warehouses'));
    }

    public function update(Request $request, Stocktake $stocktake): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'integer'],
            'ref'          => ['nullable', 'string', 'max:100'],
            'notes'        => ['nullable', 'string'],
            'status'       => ['nullable', 'in:draft,in_progress,final'],
        ]);

        $stocktake->update($data);

        if ($request->expectsJson()) {
            return response()->json($stocktake->fresh());
        }

        return redirect()->route('dashboard.inventory.stocktakes.show', $stocktake)
            ->with('success', __('Stocktake updated.'));
    }

    public function destroy(Request $request, Stocktake $stocktake): RedirectResponse|JsonResponse
    {
        $stocktake->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Deleted.']);
        }

        return redirect()->route('dashboard.inventory.stocktakes.index')
            ->with('success', __('Stocktake deleted.'));
    }

    public function finalize(Request $request, Stocktake $stocktake): RedirectResponse|JsonResponse
    {
        if ($stocktake->status === 'final') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Stocktake already finalized.'], 422);
            }
            return redirect()->route('dashboard.inventory.stocktakes.show', $stocktake)
                ->with('error', __('Stocktake has already been finalized.'));
        }

        $data = $request->validate([
            'adjustment_reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($stocktake, $data) {
            foreach ($stocktake->lines as $line) {
                $current  = $this->stockService->onHand($line->item_id, $stocktake->warehouse_id);
                $variance = $line->counted_qty - $current;
                $line->update([
                    'expected_qty' => $current,
                    'variance'     => $variance,
                ]);

                if ($variance !== 0) {
                    $this->stockService->recordMovement([
                        'company_id'      => $stocktake->company_id,
                        'created_by'      => $stocktake->created_by,
                        'item_id'         => $line->item_id,
                        'warehouse_id'    => $stocktake->warehouse_id,
                        'type'            => 'adjust',
                        'qty_change'      => $variance,
                        'reference'       => $stocktake->ref ?? "ST-{$stocktake->id}",
                        'note'            => $data['adjustment_reason'] ?? 'Stocktake finalization adjustment',
                        'movement_reason' => 'stocktake',
                    ]);
                }
            }

            $stocktake->update([
                'status'            => 'final',
                'finalized_by'      => auth()->id(),
                'finalized_at'      => now(),
                'adjustment_reason' => $data['adjustment_reason'] ?? null,
            ]);
        });

        // Emit variance signals
        $signalService = app(\App\Services\Inventory\ReorderSignalService::class);
        $signalService->detectVariances($stocktake->fresh(['lines.item']));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Stocktake finalized.']);
        }

        return redirect()->route('dashboard.inventory.stocktakes.show', $stocktake)
            ->with('success', __('Stocktake finalized and adjustments applied.'));
    }
}
