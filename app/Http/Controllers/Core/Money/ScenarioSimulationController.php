<?php
declare(strict_types=1);
namespace App\Http\Controllers\Core\Money;
use App\Http\Controllers\Controller;
use App\Services\TitanMoney\FinancialActionRecommendationService;
use App\Services\TitanMoney\ScenarioSimulationService;
use Illuminate\Http\Request;

class ScenarioSimulationController extends Controller
{
    public function __construct(
        protected ScenarioSimulationService $simulator,
        protected FinancialActionRecommendationService $recommendations,
    ) {}

    public function index(Request $request)
    {
        $scenarioTypes = [
            'supplier_price_increase', 'labor_rate_increase', 'staff_shortage',
            'lower_utilization', 'new_recurring_jobs', 'customer_churn',
            'fuel_cost_spike', 'delayed_collections', 'reduced_scheduling',
            'reorder_timing_change',
        ];
        $result = null;
        return view('default.panel.user.money.scenarios.index', compact('scenarioTypes', 'result'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'scenario_type' => 'required|string',
            'parameters'    => 'nullable|array',
            'horizon_days'  => 'nullable|integer|min:7|max:365',
        ]);
        $companyId = $request->user()->company_id;
        $result    = $this->simulator->simulate(
            $companyId,
            $validated['scenario_type'],
            $validated['parameters'] ?? [],
            $validated['horizon_days'] ?? 90,
        );
        $this->recommendations->generateFromScenario($companyId, $result);

        $scenarioTypes = [
            'supplier_price_increase', 'labor_rate_increase', 'staff_shortage',
            'lower_utilization', 'new_recurring_jobs', 'customer_churn',
            'fuel_cost_spike', 'delayed_collections', 'reduced_scheduling',
            'reorder_timing_change',
        ];
        return view('default.panel.user.money.scenarios.index', compact('scenarioTypes', 'result'));
    }
}
