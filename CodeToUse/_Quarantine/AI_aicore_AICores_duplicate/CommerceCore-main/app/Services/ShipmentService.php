<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\Courier;
use App\Models\CourierPayment;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    /**
     * Dispatch an order via a courier.
     */
    public function dispatchOrder(Order $order, $courierId, $trackingNumber = null, $shippingCost = 0)
    {
        return DB::transaction(function () use ($order, $courierId, $trackingNumber, $shippingCost) {
            
            $shipment = Shipment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier_id' => $courierId,
                    'tracking_number' => $trackingNumber,
                    'status' => 'picked',
                    'shipping_cost' => $shippingCost,
                    'shipped_at' => now(),
                ]
            );

            $order->update([
                'lifecycle_status' => 'SHIPPED',
                'status' => 'shipped'
            ]);

            // Deduct from courier balance if we track it as a liability or asset?
            // Usually, shipping cost is an outflow from our perspective.
            if ($shippingCost > 0) {
                $this->adjustCourierBalance($courierId, $shippingCost, 'outflow', "Shipping for Order #{$order->order_number}");
            }

            return $shipment;
        });
    }

    /**
     * Update shipment status and sync with Order.
     */
    public function updateStatus(Shipment $shipment, $status)
    {
        return DB::transaction(function () use ($shipment, $status) {
            $shipment->update(['status' => $status]);

            if ($status === 'delivered') {
                $shipment->update(['delivered_at' => now()]);
                $shipment->order->update([
                    'lifecycle_status' => 'DELIVERED',
                    'status' => 'delivered'
                ]);
            }

            if ($status === 'returned') {
                $shipment->order->update([
                    'lifecycle_status' => 'CANCELLED', // or RETURNED if we add it
                    'status' => 'cancelled'
                ]);
            }

            return $shipment;
        });
    }

    /**
     * Adjust courier balance.
     */
    public function adjustCourierBalance($courierId, $amount, $type, $notes = null)
    {
        $courier = Courier::findOrFail($courierId);

        if ($type === 'inflow') {
            $courier->increment('balance', $amount);
        } else {
            $courier->decrement('balance', $amount);
        }

        return CourierPayment::create([
            'courier_id' => $courierId,
            'amount' => $amount,
            'type' => $type,
            'notes' => $notes
        ]);
    }
}
