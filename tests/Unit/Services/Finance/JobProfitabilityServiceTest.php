<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Finance;

use App\Models\Finance\JobCostRecord;
use App\Models\Finance\JobRevenueRecord;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Services\Finance\JobProfitabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class JobProfitabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private JobProfitabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new JobProfitabilityService();
    }

    private function makeJob(int $companyId = 88): ServiceJob
    {
        $site = Site::factory()->create(['company_id' => $companyId]);

        return ServiceJob::factory()->create([
            'company_id' => $companyId,
            'site_id'    => $site->id,
        ]);
    }

    private function addCost(ServiceJob $job, float $amount): void
    {
        JobCostRecord::create([
            'company_id' => $job->company_id,
            'job_id'     => $job->id,
            'cost_type'  => 'labour',
            'quantity'   => 1,
            'unit_cost'  => $amount,
            'total_cost' => $amount,
            'cost_date'  => now()->toDateString(),
            'is_billable' => true,
        ]);
    }

    private function addRevenue(ServiceJob $job, float $amount): void
    {
        JobRevenueRecord::create([
            'company_id'     => $job->company_id,
            'job_id'         => $job->id,
            'revenue_type'   => 'labour',
            'quantity'       => 1,
            'unit_price'     => $amount,
            'total_revenue'  => $amount,
            'billing_source' => 'ad_hoc',
            'is_invoiced'    => false,
        ]);
    }

    public function test_calculates_profitable_summary(): void
    {
        Event::fake();

        $job = $this->makeJob();
        $this->addCost($job, 100.00);
        $this->addRevenue($job, 200.00);

        $summary = $this->service->calculateSummary($job);

        $this->assertTrue((bool) $summary->is_profitable);
        $this->assertEqualsWithDelta(100.00, (float) $summary->gross_margin, 0.01);
    }

    public function test_calculates_unprofitable_summary(): void
    {
        Event::fake();

        $job = $this->makeJob();
        $this->addCost($job, 300.00);
        $this->addRevenue($job, 100.00);

        $summary = $this->service->calculateSummary($job);

        $this->assertFalse((bool) $summary->is_profitable);
        $this->assertEqualsWithDelta(-200.00, (float) $summary->gross_margin, 0.01);
    }

    public function test_zero_revenue_flagged_unprofitable(): void
    {
        Event::fake();

        $job = $this->makeJob();
        $this->addCost($job, 150.00);
        // No revenue

        $summary = $this->service->calculateSummary($job);

        $this->assertFalse((bool) $summary->is_profitable);
    }

    public function test_is_job_profitable(): void
    {
        Event::fake();

        $job = $this->makeJob();
        $this->addCost($job, 50.00);
        $this->addRevenue($job, 200.00);

        $this->assertTrue($this->service->isJobProfitable($job));
    }
}
