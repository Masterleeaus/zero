<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Store;
use App\Services\IntelligenceService;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Expense;

class IntelligenceController extends Controller
{
    use ResolvesStore;

    public function __construct(private IntelligenceService $intelligenceService) {}

    public function index(Request $request)
    {
        $store = $this->getActiveStore($request);

        $trends     = $this->intelligenceService->getTrends($store->id);
        $suggestion = $this->intelligenceService->generateCampaignSuggestion($store);

        $rankStats = User::where('role', 'customer')
            ->where('store_id', $store->id)
            ->select('customer_rank', DB::raw('count(*) as count'))
            ->groupBy('customer_rank')
            ->get();

        // Accounting Intelligence — scoped to this store
        $grossRevenue  = Order::where('store_id', $store->id)->where('status', '!=', 'cancelled')->sum('total_price');
        $totalExpenses = Expense::where('store_id', $store->id)->sum('amount');
        $netProfit     = $grossRevenue - $totalExpenses;

        $accounting = [
            'gross_revenue'   => $grossRevenue,
            'total_expenses'  => $totalExpenses,
            'net_profit'      => $netProfit,
            'profit_margin'   => $grossRevenue > 0 ? round(($netProfit / $grossRevenue) * 100, 1) : 0,
        ];

        $weeklySales = [];
        $labels      = [];
        for ($i = 6; $i >= 0; $i--) {
            $date      = now()->subDays($i);
            $labels[]  = $date->format('D');
            $weeklySales[] = (float) Order::where('store_id', $store->id)
                ->where('status', '!=', 'cancelled')
                ->whereDate('created_at', $date->toDateString())
                ->sum('total_price');
        }

        return view('admin.intelligence.index', compact(
            'trends', 'rankStats', 'accounting', 'suggestion', 'store', 'weeklySales', 'labels'
        ));
    }

    public function generateCampaign(Request $request)
    {
        $store      = $this->getActiveStore($request);
        $suggestion = $this->intelligenceService->generateCampaignSuggestion($store);

        return response()->json([
            'success'  => true,
            'campaign' => [
                'name'          => $suggestion['name'],
                'target'        => $suggestion['target_audience'],
                'discount'      => $suggestion['suggested_discount'],
                'reason'        => $suggestion['ai_rationale'],
                'predicted_roi' => $suggestion['predicted_conversion'],
            ],
        ]);
    }

    public function customers(Request $request)
    {
        $store     = $this->getActiveStore($request);
        $customers = User::where('role', 'customer')
            ->where('store_id', $store->id)
            ->orderBy('total_spent', 'desc')
            ->paginate(30);

        return view('admin.intelligence.customers', compact('customers'));
    }
}
