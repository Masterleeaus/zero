<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\PurchaseOrder;
use App\Services\TitanMoney\SupplierBillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class APBridgeController extends Controller
{
    public function __construct(private readonly SupplierBillService $billService) {}

    public function createBillFromPO(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse|JsonResponse
    {
        $bill = $this->billService->createFromPurchaseOrder($purchaseOrder);

        if ($request->expectsJson()) {
            return response()->json($bill, 201);
        }

        return redirect()->route('dashboard.money.supplier-bills.show', $bill)
            ->with('success', __('Supplier bill created from purchase order.'));
    }
}
