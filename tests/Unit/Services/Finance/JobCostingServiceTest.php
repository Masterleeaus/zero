<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Finance;

use App\Models\Finance\JobCostRecord;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Services\Finance\JobCostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class JobCostingServiceTest extends TestCase
{
    use RefreshDatabase;

    private JobCostingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new JobCostingService();
    }

    private function makeJob(int $companyId = 99): ServiceJob
    {
        $site = Site::factory()->create(['company_id' => $companyId]);

        return ServiceJob::factory()->create([
            'company_id' => $companyId,
            'site_id'    => $site->id,
        ]);
    }

    public function test_records_labour_cost(): void
    {
        Event::fake();

        $job  = $this->makeJob();
        $tech = User::factory()->create(['company_id' => $job->company_id]);

        $record = $this->service->recordLabourCost($job, $tech, 2.5, 80.0);

        $this->assertInstanceOf(JobCostRecord::class, $record);
        $this->assertDatabaseHas('job_cost_records', [
            'job_id'    => $job->id,
            'cost_type' => 'labour',
            'quantity'  => '2.500',
            'unit_cost' => '80.0000',
            'total_cost' => '200.00',
        ]);
    }

    public function test_records_travel_cost(): void
    {
        Event::fake();

        $job = $this->makeJob();

        $record = $this->service->recordTravelCost($job, 50.0, 0.80);

        $this->assertInstanceOf(JobCostRecord::class, $record);
        $this->assertDatabaseHas('job_cost_records', [
            'job_id'     => $job->id,
            'cost_type'  => 'travel',
            'total_cost' => '40.00',
        ]);
    }

    public function test_gets_total_cost(): void
    {
        Event::fake();

        $job  = $this->makeJob();
        $tech = User::factory()->create(['company_id' => $job->company_id]);

        $this->service->recordLabourCost($job, $tech, 2.0, 100.0);   // 200.00
        $this->service->recordTravelCost($job, 100.0, 0.50);          // 50.00

        $total = $this->service->getTotalCost($job);

        $this->assertEqualsWithDelta(250.00, $total, 0.01);
    }

    public function test_gets_cost_breakdown(): void
    {
        Event::fake();

        $job  = $this->makeJob();
        $tech = User::factory()->create(['company_id' => $job->company_id]);

        $this->service->recordLabourCost($job, $tech, 1.0, 100.0);
        $this->service->recordMaterialsCost($job, [
            ['description' => 'Widget', 'quantity' => 2, 'unit_cost' => 25.0],
        ]);

        $breakdown = $this->service->getCostBreakdown($job);

        $this->assertArrayHasKey('labour', $breakdown);
        $this->assertArrayHasKey('materials', $breakdown);
        $this->assertEqualsWithDelta(100.0, $breakdown['labour']['total'], 0.01);
        $this->assertEqualsWithDelta(50.0, $breakdown['materials']['total'], 0.01);
    }
}
