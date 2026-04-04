<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\Work\FieldServiceAgreementActivated;
use App\Events\Work\FieldServiceAgreementCancelled;
use App\Events\Work\FieldServiceAgreementCreated;
use App\Events\Work\FieldServiceAgreementExpired;
use App\Events\Work\FieldServiceAgreementRenewed;
use App\Models\Crm\Customer;
use App\Models\Money\Quote;
use App\Models\Premises\Premises;
use App\Models\Work\FieldServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlanVisit;
use App\Services\Work\FieldServiceAgreementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Feature tests for fieldservice_sale_agreement + fieldservice_sale_recurring merge.
 *
 * Validates the contract-driven service lifecycle pipeline:
 *   Quote → FieldServiceAgreement → ServicePlanVisits → ServiceJobs
 */
class FieldServiceAgreementTest extends TestCase
{
    use RefreshDatabase;

    private int $company = 1;

    private function makeSvc(): FieldServiceAgreementService
    {
        return new FieldServiceAgreementService();
    }

    private function makeCustomer(): Customer
    {
        return Customer::factory()->create(['company_id' => $this->company]);
    }

    private function makeQuote(Customer $customer): Quote
    {
        return Quote::factory()->create([
            'company_id'  => $this->company,
            'customer_id' => $customer->id,
            'status'      => 'accepted',
        ]);
    }

    // ── createAgreementFromQuote ──────────────────────────────────────────────

    public function test_creates_agreement_from_quote(): void
    {
        Event::fake([FieldServiceAgreementCreated::class]);

        $customer = $this->makeCustomer();
        $quote    = $this->makeQuote($customer);
        $svc      = $this->makeSvc();

        $agreement = $svc->createAgreementFromQuote($quote, [
            'billing_cycle'     => 'quarterly',
            'service_frequency' => 'monthly',
        ]);

        $this->assertDatabaseHas('field_service_agreements', [
            'company_id'     => $this->company,
            'customer_id'    => $customer->id,
            'quote_id'       => $quote->id,
            'status'         => 'active',
            'billing_cycle'  => 'quarterly',
        ]);

        Event::assertDispatched(FieldServiceAgreementCreated::class, function ($e) use ($agreement) {
            return $e->agreement->id === $agreement->id;
        });
    }

    // ── generateVisitsFromAgreement ───────────────────────────────────────────

    public function test_generates_projected_visits_from_agreement(): void
    {
        $agreement = FieldServiceAgreement::factory()->create([
            'company_id'        => $this->company,
            'start_date'        => now()->toDateString(),
            'end_date'          => now()->addMonths(3)->toDateString(),
            'service_frequency' => 'monthly',
            'status'            => 'active',
        ]);

        $svc    = $this->makeSvc();
        $visits = $svc->generateVisitsFromAgreement($agreement, 3);

        $this->assertCount(3, $visits);

        // Assert backing ServicePlan was auto-created
        $this->assertDatabaseHas('service_plans', [
            'company_id'                 => $this->company,
            'field_service_agreement_id' => $agreement->id,
        ]);

        // Assert visits are linked to the backing plan (satisfying NOT NULL FK)
        $visit = $visits->first();
        $this->assertNotNull($visit->service_plan_id);

        $this->assertDatabaseHas('service_plan_visits', [
            'field_service_agreement_id' => $agreement->id,
            'coverage_source'            => 'agreement',
            'status'                     => 'pending',
        ]);
    }

    // ── generateJobsFromAgreement ─────────────────────────────────────────────

    public function test_generates_jobs_from_pending_visits(): void
    {
        $customer  = $this->makeCustomer();
        $agreement = FieldServiceAgreement::factory()->create([
            'company_id'  => $this->company,
            'customer_id' => $customer->id,
            'status'      => 'active',
        ]);

        $svc = $this->makeSvc();
        $svc->generateVisitsFromAgreement($agreement, 2);

        $jobs = $svc->generateJobsFromAgreement($agreement);

        $this->assertCount(2, $jobs);

        foreach ($jobs as $job) {
            $this->assertEquals($agreement->id, $job->recurring_source_id);
            $this->assertNotNull($job->contract_visit_id);
        }
    }

    // ── activateAgreement ─────────────────────────────────────────────────────

    public function test_activates_draft_agreement(): void
    {
        Event::fake([FieldServiceAgreementActivated::class]);

        $agreement = FieldServiceAgreement::factory()->draft()->create([
            'company_id' => $this->company,
        ]);

        $this->assertEquals('draft', $agreement->status);

        $svc = $this->makeSvc();
        $svc->activateAgreement($agreement);

        $this->assertEquals('active', $agreement->fresh()->status);
        Event::assertDispatched(FieldServiceAgreementActivated::class);
    }

    // ── terminateAgreement ────────────────────────────────────────────────────

    public function test_terminates_active_agreement(): void
    {
        Event::fake([FieldServiceAgreementCancelled::class]);

        $agreement = FieldServiceAgreement::factory()->active()->create([
            'company_id' => $this->company,
        ]);

        $svc = $this->makeSvc();
        $svc->terminateAgreement($agreement, 'Customer request');

        $this->assertEquals('cancelled', $agreement->fresh()->status);

        Event::assertDispatched(FieldServiceAgreementCancelled::class, function ($e) {
            return $e->reason === 'Customer request';
        });
    }

    // ── expireAgreement ───────────────────────────────────────────────────────

    public function test_expires_agreement(): void
    {
        Event::fake([FieldServiceAgreementExpired::class]);

        $agreement = FieldServiceAgreement::factory()->active()->create([
            'company_id' => $this->company,
        ]);

        $svc = $this->makeSvc();
        $svc->expireAgreement($agreement);

        $this->assertEquals('expired', $agreement->fresh()->status);
        Event::assertDispatched(FieldServiceAgreementExpired::class);
    }

    // ── renewAgreement ────────────────────────────────────────────────────────

    public function test_renews_agreement_and_creates_successor(): void
    {
        Event::fake([FieldServiceAgreementRenewed::class, FieldServiceAgreementCreated::class]);

        $agreement = FieldServiceAgreement::factory()->active()->create([
            'company_id' => $this->company,
            'start_date' => now()->subYear()->toDateString(),
            'end_date'   => now()->toDateString(),
        ]);

        $svc     = $this->makeSvc();
        $renewal = $svc->renewAgreement($agreement);

        $this->assertEquals('renewed', $agreement->fresh()->status);
        $this->assertEquals('active', $renewal->status);
        $this->assertEquals($agreement->customer_id, $renewal->customer_id);

        Event::assertDispatched(FieldServiceAgreementRenewed::class, function ($e) use ($agreement, $renewal) {
            return $e->agreement->id === $agreement->id && $e->renewal->id === $renewal->id;
        });
    }

    // ── Model helpers ─────────────────────────────────────────────────────────

    public function test_is_expiring_returns_true_when_ending_within_window(): void
    {
        $agreement = FieldServiceAgreement::factory()->make([
            'status'   => 'active',
            'end_date' => now()->addDays(10),
        ]);

        $this->assertTrue($agreement->isExpiring(30));
        $this->assertFalse($agreement->isExpiring(5));
    }

    public function test_execution_summary_counts_jobs_and_visits(): void
    {
        $customer  = $this->makeCustomer();
        $agreement = FieldServiceAgreement::factory()->create([
            'company_id'  => $this->company,
            'customer_id' => $customer->id,
            'status'      => 'active',
        ]);

        $svc = $this->makeSvc();
        $svc->generateVisitsFromAgreement($agreement, 2);
        $svc->generateJobsFromAgreement($agreement);

        $summary = $agreement->fresh()->executionSummary();

        $this->assertEquals($agreement->id, $summary['agreement_id']);
        $this->assertEquals(2, $summary['total_jobs']);
        $this->assertEquals(0, $summary['completed_jobs']);
        $this->assertEquals(2, $summary['total_visits']);
    }

    // ── Customer helpers ──────────────────────────────────────────────────────

    public function test_customer_active_service_contracts_returns_active_agreements(): void
    {
        $customer = $this->makeCustomer();

        FieldServiceAgreement::factory()->create([
            'company_id'  => $this->company,
            'customer_id' => $customer->id,
            'status'      => 'active',
        ]);

        FieldServiceAgreement::factory()->create([
            'company_id'  => $this->company,
            'customer_id' => $customer->id,
            'status'      => 'expired',
        ]);

        $contracts = $customer->activeServiceContracts();
        $this->assertCount(1, $contracts);
        $this->assertEquals('active', $contracts->first()->status);
    }

    public function test_customer_expiring_service_contracts(): void
    {
        $customer = $this->makeCustomer();

        FieldServiceAgreement::factory()->create([
            'company_id'  => $this->company,
            'customer_id' => $customer->id,
            'status'      => 'active',
            'end_date'    => now()->addDays(10)->toDateString(),
        ]);

        FieldServiceAgreement::factory()->create([
            'company_id'  => $this->company,
            'customer_id' => $customer->id,
            'status'      => 'active',
            'end_date'    => now()->addDays(60)->toDateString(),
        ]);

        $expiring = $customer->expiringServiceContracts(30);
        $this->assertCount(1, $expiring);
    }

    // ── ServiceJob linkage ────────────────────────────────────────────────────

    public function test_service_job_recurring_source_relationship(): void
    {
        $customer  = $this->makeCustomer();
        $agreement = FieldServiceAgreement::factory()->create([
            'company_id'  => $this->company,
            'customer_id' => $customer->id,
            'status'      => 'active',
        ]);

        $svc = $this->makeSvc();
        $svc->generateVisitsFromAgreement($agreement, 1);
        $jobs = $svc->generateJobsFromAgreement($agreement);

        $job = $jobs->first();
        $this->assertNotNull($job);
        $this->assertEquals($agreement->id, $job->recurringSource->id);
        $this->assertNotNull($job->contractVisit);
    }

    // ── ServicePlanVisit linkage ──────────────────────────────────────────────

    public function test_service_plan_visit_field_service_agreement_relationship(): void
    {
        $agreement = FieldServiceAgreement::factory()->create([
            'company_id' => $this->company,
            'status'     => 'active',
        ]);

        $svc    = $this->makeSvc();
        $visits = $svc->generateVisitsFromAgreement($agreement, 1);

        $visit = $visits->first()->fresh();
        $this->assertEquals($agreement->id, $visit->fieldServiceAgreement->id);
    }
}
