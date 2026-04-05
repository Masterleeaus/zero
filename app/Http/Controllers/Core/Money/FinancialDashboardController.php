<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Controller;
use App\Services\TitanMoney\CashflowService;
use App\Services\TitanMoney\FinancialKpiService;
use App\Services\TitanMoney\FinancialSnapshotService;
use App\Services\TitanMoney\ForecastingService;
use App\Services\TitanMoney\ProfitabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialDashboardController extends Controller
{
    public function __construct(
        protected FinancialSnapshotService $snapshot,
        protected CashflowService $cashflow,
        protected ForecastingService $forecasting,
        protected FinancialKpiService $kpis,
        protected ProfitabilityService $profitability,
    ) {}

    /**
     * GET money/dashboard
     * Overview snapshot + quick KPIs.
     */
    public function dashboard(Request $request)
    {
        $companyId = $request->user()->company_id;
        $snapshot  = $this->snapshot->snapshot($companyId);
        $kpis      = $this->kpis->compute($companyId);

        return view('default.panel.user.money.dashboard.index', compact('snapshot', 'kpis'));
    }

    /**
     * GET money/cashflow
     * Cash flow chart data and projection tables.
     */
    public function cashflow(Request $request)
    {
        $companyId = $request->user()->company_id;
        $weekly    = $this->cashflow->weeklyProjection($companyId, 4);
        $monthly   = $this->cashflow->monthlyProjection($companyId, 3);
        $rolling90 = $this->cashflow->rolling90Day($companyId);

        return view('default.panel.user.money.dashboard.cashflow', compact('weekly', 'monthly', 'rolling90'));
    }

    /**
     * GET money/forecast
     * Revenue / cost / margin / runway forecast.
     */
    public function forecast(Request $request)
    {
        $companyId   = $request->user()->company_id;
        $forecast30  = $this->forecasting->forecast30($companyId);
        $forecast90  = $this->forecasting->forecast90($companyId);
        $forecast12m = $this->forecasting->forecast12Month($companyId);

        return view('default.panel.user.money.dashboard.forecast', compact('forecast30', 'forecast90', 'forecast12m'));
    }

    /**
     * GET money/kpis
     * KPI tile page.
     */
    public function kpis(Request $request)
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : now()->subDays(30)->startOfDay();
        $to   = isset($validated['to'])   ? Carbon::parse($validated['to'])   : now()->endOfDay();

        $kpis = $this->kpis->compute($companyId, $from, $to);

        return view('default.panel.user.money.dashboard.kpis', compact('kpis'));
    }

    /**
     * GET money/job-profitability
     * Profitability grid across jobs, sites, teams, customers.
     */
    public function jobProfitability(Request $request)
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : now()->subDays(30)->startOfDay();
        $to   = isset($validated['to'])   ? Carbon::parse($validated['to'])   : now()->endOfDay();

        $byPeriod = $this->profitability->forPeriod($companyId, $from, $to);

        return view('default.panel.user.money.dashboard.job-profitability', compact('byPeriod', 'from', 'to'));
    }
}
