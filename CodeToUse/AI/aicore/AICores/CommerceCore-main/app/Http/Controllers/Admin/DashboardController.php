<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Product;
use App\Models\NewsletterSubscriber;
use App\Models\ContactSubmission;
use App\Models\AiInsight;
use App\Models\SystemAlert;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct(private StoreService $storeService) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $stores = Store::with('owner', 'plan')->latest()->get();
            $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_price');
            $totalOrders = Order::count();
            $totalStores = Store::count();
            $recentOrders = Order::with('store')->latest()->take(10)->get();

            // Store growth trend (last 30 days)
            $storeGrowth = collect();
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $storeGrowth->push([
                    'date' => $date->format('M d'),
                    'count' => Store::whereDate('created_at', '<=', $date)->count(),
                    'new' => Store::whereDate('created_at', $date)->count()
                ]);
            }

            // Revenue by store (top 5)
            $topStores = Store::withCount(['orders as total_sales' => function($query) {
                $query->where('status', '!=', 'cancelled')->select(\Illuminate\Support\Facades\DB::raw('COALESCE(SUM(total_price), 0)'));
            }])
            ->orderByDesc('total_sales')
            ->take(5)
            ->get();

            // Plan distribution (Assuming Store has a 'plan' relationship or plan_id)
            // Using group by on plan_id for a basic distribution if plans exist
            $planDistribution = Store::whereNotNull('plan_id')
                ->select('plan_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->with('plan')
                ->groupBy('plan_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->plan ? $item->plan->name : 'Unknown',
                        'count' => $item->count
                    ];
                });

            // If no plan distribution found (maybe no subscriptions seeded), do default
            if ($planDistribution->isEmpty()) {
                $planDistribution = collect([['name' => 'Free', 'count' => $totalStores]]);
            }

            // Platform KPIs
            $mrr = Store::whereNotNull('plan_id')->get()->sum(function($store) {
                return $store->plan ? $store->plan->price : 0;
            });
            $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
            $totalCustomers = \App\Models\User::where('role', 'customer')->count();

            return view('admin.dashboard', compact(
                'stores', 'totalRevenue', 'totalOrders', 'totalStores', 'recentOrders',
                'storeGrowth', 'topStores', 'planDistribution', 'mrr', 'avgOrderValue', 'totalCustomers'
            ));
        }

        // Store owner/staff dashboard
        $store = $request->get('admin_store') ?? $user->ownedStores()->first();
        if (!$store) {
            return redirect()->route('admin.stores.create');
        }

        $storeId = $store->id;
        $stats = $this->storeService->getStats($store);
        $recentOrders = $store->orders()->with('items.product')->latest()->take(5)->get();

        // ========== SALES ==========
        $totalSales = Order::where('store_id', $storeId)->where('status', '!=', 'cancelled')->sum('total_price');
        $salePaid = Order::where('store_id', $storeId)->whereIn('status', ['paid', 'delivered', 'shipped'])->sum('total_price');
        $saleDue = $totalSales - $salePaid;

        // ========== PURCHASES ==========
        $totalPurchase = Purchase::where('store_id', $storeId)->where('status', '!=', 'cancelled')->sum('total_amount');
        $purchasePaid = Purchase::where('store_id', $storeId)->where('payment_status', 'paid')->where('status', '!=', 'cancelled')->sum('total_amount');
        $purchaseDue = $totalPurchase - $purchasePaid;

        // ========== EXPENSE ==========
        $totalExpense = Expense::where('store_id', $storeId)->sum('amount');

        // ========== SALARY ==========
        $totalSalary = Payroll::whereHas('employee', fn($q) => $q->whereHas('store', fn($s) => $s->where('id', $storeId)))->sum('net_salary');
        $salaryPaid = Payroll::whereHas('employee', fn($q) => $q->whereHas('store', fn($s) => $s->where('id', $storeId)))->where('status', 'paid')->sum('net_salary');
        $salaryDue = $totalSalary - $salaryPaid;

        // ========== RETURNS (orders cancelled after payment) ==========
        $saleReturn = Order::where('store_id', $storeId)->where('status', 'cancelled')->sum('total_price');
        $purchaseReturn = Purchase::where('store_id', $storeId)->where('status', 'cancelled')->sum('total_amount');

        // ========== PROFIT ==========
        $profit = $totalSales - $totalPurchase - $totalExpense - $totalSalary - $saleReturn + $purchaseReturn;

        // ========== WEEKLY REVENUE (7d) ==========
        $weeklyRevenue = [];
        $weeklyExpense = [];
        $dayLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayLabels[] = $date->format('D');
            $weeklyRevenue[] = (float) $store->orders()
                ->where('status', '!=', 'cancelled')
                ->whereDate('created_at', $date->toDateString())
                ->sum('total_price');
            $weeklyExpense[] = (float) Expense::where('store_id', $storeId)
                ->whereDate('date', $date->toDateString())
                ->sum('amount');
        }

        // ========== MONTHLY TREND (6 months) ==========
        $monthlyData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $label = $monthStart->format('M');
            $mSales = (float) Order::where('store_id', $storeId)->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_price');
            $mPurchase = (float) Purchase::where('store_id', $storeId)->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_amount');
            $mExpense = (float) Expense::where('store_id', $storeId)
                ->whereBetween('date', [$monthStart, $monthEnd])->sum('amount');
            $monthlyData->push([
                'label' => $label,
                'sales' => $mSales,
                'purchase' => $mPurchase,
                'expense' => $mExpense,
                'profit' => $mSales - $mPurchase - $mExpense,
            ]);
        }

        // ========== TOP CATEGORIES ==========
        $topCategories = Category::where('store_id', $storeId)
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(5)
            ->get();

        // ========== EXPENSE BREAKDOWN ==========
        $expenseBreakdown = Expense::where('store_id', $storeId)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->take(6)
            ->get();

        // ========== ORDER STATUS DISTRIBUTION ==========
        $orderStatusDist = Order::where('store_id', $storeId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // ========== TOP SELLING PRODUCTS ==========
        $topSellingProducts = \App\Models\OrderItem::with('product')
            ->whereHas('order', function($q) use ($storeId) {
                $q->where('store_id', $storeId)->where('status', '!=', 'cancelled');
            })
            ->select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_sold'), \Illuminate\Support\Facades\DB::raw('SUM(quantity * price) as revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // ========== LOW STOCK ALERTS ==========
        $lowStockProducts = Product::where('store_id', $storeId)
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        // ========== CRM KPIs ==========
        $totalSubscribers = NewsletterSubscriber::where('store_id', $storeId)->count();
        $totalInquiries = ContactSubmission::where('store_id', $storeId)->count();
        $newInquiriesCount = ContactSubmission::where('store_id', $storeId)->where('status', 'new')->count();

        // ========== AI INSIGHTS & SYSTEM ALERTS ==========
        $aiInsights = AiInsight::where('store_id', $storeId)
            ->where('status', 'new')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $systemAlerts = SystemAlert::where('store_id', $storeId)
            ->where('status', 'active')
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 WHEN 'info' THEN 3 ELSE 4 END")
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'store', 'stats', 'recentOrders',
            'totalSales', 'salePaid', 'saleDue',
            'totalPurchase', 'purchasePaid', 'purchaseDue',
            'totalExpense', 'totalSalary', 'salaryPaid', 'salaryDue',
            'saleReturn', 'purchaseReturn', 'profit',
            'dayLabels', 'weeklyRevenue', 'weeklyExpense',
            'monthlyData', 'topCategories', 'expenseBreakdown', 'orderStatusDist',
            'topSellingProducts', 'lowStockProducts',
            'totalSubscribers', 'totalInquiries', 'newInquiriesCount',
            'aiInsights', 'systemAlerts'
        ));
    }
}
