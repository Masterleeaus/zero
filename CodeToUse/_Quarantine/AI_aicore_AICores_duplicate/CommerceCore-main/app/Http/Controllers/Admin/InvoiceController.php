<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ResolvesStore;

    /**
     * Show the invoice for an order (A4 or Thermal).
     * ✅ Fixed: Added tenant ownership check before returning the invoice.
     */
    public function show(Request $request, Order $order, string $type = 'a4')
    {
        // Authorization: ensure invoice belongs to the active store
        if (!$request->user()->isSuperAdmin()) {
            $store = $this->getActiveStore($request);
            if ($order->store_id !== $store->id) {
                abort(403, 'You do not have access to this invoice.');
            }
        }

        $order->load(['items.product', 'store', 'user', 'assignedAgent']);

        if ($type === 'thermal') {
            return view('admin.invoices.thermal', compact('order'));
        }

        return view('admin.invoices.a4', compact('order'));
    }
}
