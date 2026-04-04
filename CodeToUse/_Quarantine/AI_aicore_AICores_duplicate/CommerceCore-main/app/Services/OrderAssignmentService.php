<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderAssignmentService
{
    /**
     * Finds an unassigned order and assigns it to an active agent.
     * Uses pessimistic locking to prevent duplicate assignments across concurrent requests.
     */
    public function assignNextAvailableOrder(User $agent): ?Order
    {
        return DB::transaction(function () use ($agent) {
            // Find the oldest unassigned order and lock the row
            $order = Order::where('lifecycle_status', 'NEW')
                ->whereNull('assigned_agent_id')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return null;
            }

            $order->update([
                'lifecycle_status' => 'ASSIGNED',
                'assigned_agent_id' => $agent->id,
                'locked_at' => now(),
            ]);

            return $order;
        });
    }

    /**
     * Auto-distribute a single order to the best available agent.
     * Primarily used via a scheduled job or an event listener right after order creation.
     */
    public function autoAssignOrder(Order $order): bool
    {
        if ($order->assigned_agent_id) {
            return false;
        }

        return DB::transaction(function () use ($order) {
            // Lock the order
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            
            if ($lockedOrder->assigned_agent_id) {
                return false;
            }

            // Find agent with least number of active assignments
            // Only consider users with the 'staff' role who handle the tenant boundaries
            $agent = User::role('staff')
                ->where('store_id', $order->store_id)
                ->withCount(['orders as active_orders_count' => function ($query) {
                    $query->whereNotIn('lifecycle_status', ['DELIVERED', 'CANCELLED', 'RETURNED']);
                }])
                ->orderBy('active_orders_count', 'asc')
                ->first();

            if (!$agent) {
                return false;
            }

            $lockedOrder->update([
                'lifecycle_status' => 'ASSIGNED',
                'assigned_agent_id' => $agent->id,
                'locked_at' => now(),
            ]);

            return true;
        });
    }
}
