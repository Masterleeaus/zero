<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\OrderAssignmentService;
use Illuminate\Support\Facades\Auth;

class AgentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get active orders currently assigned to this agent
        $assignedOrders = Order::where('assigned_agent_id', $user->id)
            ->whereNotIn('lifecycle_status', ['DELIVERED', 'CANCELLED', 'RETURNED'])
            ->orderBy('locked_at', 'desc')
            ->paginate(15);

        return view('admin.agent.dashboard', compact('assignedOrders'));
    }

    public function assignOrder(OrderAssignmentService $assignmentService)
    {
        $user = Auth::user();
        
        $order = $assignmentService->assignNextAvailableOrder($user);

        if ($order) {
            return redirect()->back()->with('success', "Order #{$order->order_number} has been assigned to you.");
        }

        return redirect()->back()->with('error', 'No new orders available for assignment at the moment.');
    }
}
