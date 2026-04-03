<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Product;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ErpDashboardController extends Controller
{
    use ResolvesStore;

    public function index(Request $request)
    {
        // For super_admin: show platform-wide totals; for others: scoped to their store
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $storeId = null; // Loaded differently below
        } else {
            $store   = $this->getActiveStore($request);
            $storeId = $store->id;
        }

        // ====== FINANCIAL SUMMARY ======
        $revenueQuery  = Order::where('status', '!=', 'cancelled');
        $expenseQuery  = Expense::query();
        $purchaseQuery = Purchase::where('status', '!=', 'cancelled');
        $payrollQuery  = Payroll::query();

        if ($storeId) {
            $revenueQuery->where('store_id', $storeId);
            $expenseQuery->where('store_id', $storeId);
            $purchaseQuery->where('store_id', $storeId);
            $payrollQuery->whereHas('employee', fn($q) => $q->where('store_id', $storeId));
        }

        $revenue    = (float) $revenueQuery->sum('total_price');
        $expenses   = (float) $expenseQuery->sum('amount') + (float) $purchaseQuery->sum('total_amount');
        $netProfit  = $revenue - $expenses;

        // A/R aging (orders by status approximation)
        $arAging = [
            'current' => (float) Order::when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->where('status', 'paid')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->sum('total_price'),
            'over_30' => (float) Order::when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->where('status', 'pending')
                ->whereDate('created_at', '<', now()->subDays(30))
                ->sum('total_price'),
            'over_60' => (float) Order::when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->where('status', 'pending')
                ->whereDate('created_at', '<', now()->subDays(60))
                ->sum('total_price'),
            'over_90' => (float) Order::when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->where('status', 'pending')
                ->whereDate('created_at', '<', now()->subDays(90))
                ->sum('total_price'),
        ];

        // A/P aging (purchases)
        $apAging = [
            'current' => (float) Purchase::when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->where('payment_status', 'pending')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->sum('total_amount'),
            'over_30' => (float) Purchase::when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->where('payment_status', 'pending')
                ->whereDate('created_at', '<', now()->subDays(30))
                ->sum('total_amount'),
            'over_60' => (float) Purchase::when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->where('payment_status', 'pending')
                ->whereDate('created_at', '<', now()->subDays(60))
                ->sum('total_amount'),
            'over_90' => (float) Purchase::when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->where('payment_status', 'pending')
                ->whereDate('created_at', '<', now()->subDays(90))
                ->sum('total_amount'),
        ];

        $financialSummary = [
            'revenue'    => $revenue,
            'expenses'   => $expenses,
            'net_profit' => $netProfit,
            'ar_aging'   => $arAging,
            'ap_aging'   => $apAging,
        ];

        // ====== HR SUMMARY ======
        $empQuery = Employee::query();
        if ($storeId) {
            $empQuery->where('store_id', $storeId);
        }

        $totalEmployees = $empQuery->count();
        $presentToday   = Attendance::when($storeId, function ($q) use ($storeId) {
                $q->whereHas('user', fn($eq) => $eq->where('store_id', $storeId));
            })
            ->where('date', today()->toDateString())
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->count();

        $onLeave = LeaveRequest::when($storeId, function ($q) use ($storeId) {
                $q->whereHas('employee', fn($eq) => $eq->where('store_id', $storeId));
            })
            ->where('status', 'approved')
            ->where('start_date', '<=', today()->toDateString())
            ->where('end_date', '>=', today()->toDateString())
            ->count();

        $pendingLeave = LeaveRequest::when($storeId, function ($q) use ($storeId) {
                $q->whereHas('employee', fn($eq) => $eq->where('store_id', $storeId));
            })
            ->where('status', 'pending')
            ->count();

        $hrSummary = [
            'total_employees'        => $totalEmployees,
            'present_today'          => $presentToday,
            'on_leave'               => $onLeave,
            'pending_leave_requests' => $pendingLeave,
        ];

        // ====== OPERATIONAL ALERTS ======
        $operationalAlerts = [];

        $pendingPOs = Purchase::when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->where('status', 'pending')
            ->count();
        if ($pendingPOs > 0) {
            $operationalAlerts[] = [
                'type'    => 'warning',
                'message' => "{$pendingPOs} Purchase Order(s) pending approval.",
            ];
        }

        $lowStockCount = Product::when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->whereNotNull('alert_quantity')
            ->whereColumn('stock', '<=', 'alert_quantity')
            ->count();
        if ($lowStockCount > 0) {
            $operationalAlerts[] = [
                'type'    => 'danger',
                'message' => "{$lowStockCount} inventory item(s) below minimum reorder point.",
            ];
        }

        $payrollDue = Payroll::when($storeId, function ($q) use ($storeId) {
                $q->whereHas('employee', fn($eq) => $eq->where('store_id', $storeId));
            })
            ->where('status', 'pending')
            ->where('pay_period_end', '>=', now()->subDays(3))
            ->count();
        if ($payrollDue > 0) {
            $operationalAlerts[] = [
                'type'    => 'info',
                'message' => "{$payrollDue} payroll(s) due for processing.",
            ];
        }

        if (empty($operationalAlerts)) {
            $operationalAlerts[] = [
                'type'    => 'success',
                'message' => 'All systems operational. No urgent alerts.',
            ];
        }

        return view('admin.erp.dashboard', compact('financialSummary', 'operationalAlerts', 'hrSummary'));
    }
}
