<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Services\TitanMoney\FinanceReportService;
use App\Services\TitanMoney\SupplierBillService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceReportController extends CoreController
{
    public function __construct(
        private readonly FinanceReportService $reportService,
        private readonly SupplierBillService  $supplierBillService,
    ) {}

    public function profitAndLoss(Request $request): View
    {
        $companyId   = $request->user()->company_id;
        $periodStart = $request->get('period_start', now()->startOfYear()->toDateString());
        $periodEnd   = $request->get('period_end', now()->toDateString());

        $report = $this->reportService->profitAndLoss($companyId, $periodStart, $periodEnd);

        return view('default.panel.user.money.reports.profit-and-loss', compact('report', 'periodStart', 'periodEnd'));
    }

    public function balanceSheet(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $asAt      = $request->get('as_at', now()->toDateString());

        $report = $this->reportService->balanceSheet($companyId, $asAt);

        return view('default.panel.user.money.reports.balance-sheet', compact('report', 'asAt'));
    }

    public function cashFlow(Request $request): View
    {
        $companyId   = $request->user()->company_id;
        $periodStart = $request->get('period_start', now()->startOfMonth()->toDateString());
        $periodEnd   = $request->get('period_end', now()->toDateString());

        $report = $this->reportService->cashFlow($companyId, $periodStart, $periodEnd);

        return view('default.panel.user.money.reports.cash-flow', compact('report', 'periodStart', 'periodEnd'));
    }

    public function agedReceivables(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $buckets   = $this->reportService->agedReceivables($companyId);

        return view('default.panel.user.money.reports.aged-receivables', compact('buckets'));
    }

    public function agedPayables(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $buckets   = $this->supplierBillService->agingSummary($companyId);

        return view('default.panel.user.money.reports.aged-payables', compact('buckets'));
    }

    public function jobProfitability(Request $request): View
    {
        $companyId   = $request->user()->company_id;
        $periodStart = $request->get('period_start');
        $periodEnd   = $request->get('period_end');

        $rows = $this->reportService->jobProfitability($companyId, $periodStart, $periodEnd);

        return view('default.panel.user.money.reports.job-profitability', compact('rows', 'periodStart', 'periodEnd'));
    }
}
