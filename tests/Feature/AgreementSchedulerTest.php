<?php

namespace Tests\Feature;

use App\Models\Work\ServiceAgreement;
use App\Services\Work\AgreementSchedulerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgreementSchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduler_creates_job_and_advances_next_run(): void
    {
        $agreement = ServiceAgreement::factory()->create([
            'company_id' => 5,
            'status' => 'active',
            'frequency' => 'weekly',
            'next_run_at' => now()->subDay(),
        ]);

        $service = new AgreementSchedulerService();
        $service->runForCompany(5);

        $this->assertDatabaseHas('service_jobs', [
            'agreement_id' => $agreement->id,
            'company_id' => 5,
        ]);
        $this->assertTrue($agreement->fresh()->next_run_at->greaterThan(now()));
    }
}
