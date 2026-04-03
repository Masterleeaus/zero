<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Courier;
use App\Services\ShipmentService;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    protected $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    /**
     * Display a listing of shipments.
     */
    public function index()
    {
        $shipments = Shipment::with(['order', 'courier'])->latest()->paginate(20);
        return view('admin.shipments.index', compact('shipments'));
    }

    /**
     * Dispatch an order via a courier.
     */
    public function dispatchOrder(Request $request, Order $order)
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'tracking_number' => 'nullable|string|max:100',
            'shipping_cost' => 'required|numeric|min:0'
        ]);

        try {
            $this->shipmentService->dispatchOrder(
                $order, 
                $validated['courier_id'], 
                $validated['tracking_number'], 
                $validated['shipping_cost']
            );

            return back()->with('success', "Order #{$order->order_number} dispatched successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update shipment status.
     */
    public function updateStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => 'required|in:picked,in_transit,delivered,returned,cancelled'
        ]);

        try {
            $this->shipmentService->updateStatus($shipment, $validated['status']);
            return back()->with('success', "Shipment status updated to {$validated['status']}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
