<?php

declare(strict_types=1);

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\JobRevenueRecord;
use App\Models\Work\ServiceJob;
use App\Services\Finance\FinancialRollupService;
use App\Services\Finance\JobCostingService;
use App\Services\Finance\JobProfitabilityService;
use App\Services\Finance\JobRevenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobFinanceController extends Controller
{
    public function __construct(
        private readonly JobCostingService $costing,
        private readonly JobProfitabilityService $profitability,
        private readonly JobRevenueService $revenue,
        private readonly FinancialRollupService $rollup,
    ) {}

    public function summary(Request $request, ServiceJob $job): JsonResponse
    {
        $summary = $this->profitability->calculateSummary($job);

        return response()->json($summary);
    }

    public function costs(Request $request, ServiceJob $job): JsonResponse
    {
        $breakdown = $this->costing->getCostBreakdown($job);

        return response()->json($breakdown);
    }

    public function revenue(Request $request, ServiceJob $job): JsonResponse
    {
        $total   = $this->revenue->getTotalRevenue($job);
        $records = JobRevenueRecord::withoutGlobalScopes()
            ->where('job_id', $job->id)
            ->get();

        return response()->json(['total' => $total, 'records' => $records]);
    }

    public function atRisk(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;
        $threshold = (float) $request->input('margin_threshold', 0.0);
        $jobs      = $this->profitability->getAtRiskJobs($companyId, $threshold);

        return response()->json($jobs);
    }

    public function rollup(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;
        $this->rollup->refreshAllRollups($companyId);

        return response()->json(['status' => 'ok']);
    }
}
