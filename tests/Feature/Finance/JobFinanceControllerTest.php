<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Models\Finance\JobCostRecord;
use App\Models\Finance\JobRevenueRecord;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class JobFinanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserAndJob(int $companyId = 55): array
    {
        $user = User::factory()->create(['company_id' => $companyId]);
        $site = Site::factory()->create(['company_id' => $companyId]);
        $job  = ServiceJob::factory()->create([
            'company_id' => $companyId,
            'site_id'    => $site->id,
        ]);

        return [$user, $job];
    }

    public function test_summary_endpoint_returns_json(): void
    {
        Event::fake();

        [$user, $job] = $this->makeUserAndJob();

        $this->actingAs($user)
            ->getJson(route('dashboard.finance.jobs.summary', $job))
            ->assertOk()
            ->assertJsonStructure(['job_id', 'total_cost', 'total_revenue', 'is_profitable']);
    }

    public function test_costs_endpoint_returns_json(): void
    {
        Event::fake();

        [$user, $job] = $this->makeUserAndJob();

        JobCostRecord::create([
            'company_id' => $job->company_id,
            'job_id'     => $job->id,
            'cost_type'  => 'labour',
            'quantity'   => 1.0,
            'unit_cost'  => 80.0,
            'total_cost' => 80.0,
            'cost_date'  => now()->toDateString(),
            'is_billable' => true,
        ]);

        $this->actingAs($user)
            ->getJson(route('dashboard.finance.jobs.costs', $job))
            ->assertOk()
            ->assertJsonStructure(['labour', 'materials', 'travel']);
    }

    public function test_at_risk_endpoint_returns_json(): void
    {
        Event::fake();

        [$user] = $this->makeUserAndJob();

        $this->actingAs($user)
            ->getJson(route('dashboard.finance.at-risk'))
            ->assertOk()
            ->assertJson([]);
    }

    public function test_unauthenticated_access_denied(): void
    {
        $this->getJson(route('dashboard.finance.at-risk'))
            ->assertUnauthorized();
    }
}
