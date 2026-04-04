<?php

declare(strict_types=1);

namespace App\Listeners\Finance;

use App\Events\Finance\JobCostRecorded;
use App\Models\Finance\JobFinancialSummary;
use App\Services\Finance\JobProfitabilityService;

class RecalculateFinancialSummaryOnCostChange
{
    public function __construct(private readonly JobProfitabilityService $profitability) {}

    public function handle(JobCostRecorded $event): void
    {
        $job = $event->costRecord->job()->withoutGlobalScopes()->first();

        if ($job === null) {
            return;
        }

        $this->profitability->calculateSummary($job);
    }
}
