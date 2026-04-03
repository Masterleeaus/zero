<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\Work\SaleRecurringAgreementCreated;
use App\Events\Work\SaleRecurringAgreementUpdated;
use App\Events\Work\SaleRecurringCoverageApplied;
use App\Events\Work\SaleRecurringPlanGenerated;
use App\Events\Work\SaleRecurringVisitMaterialized;
use App\Events\Work\SaleRecurringVisitProjected;
use App\Models\Crm\Customer;
use App\Models\Money\Quote;
use App\Models\Money\QuoteItem;
use App\Models\Premises\Premises;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServicePlan;
use App\Models\Work\ServicePlanVisit;
use App\Services\Work\FieldServiceSaleService;
use App\Services\Work\SaleRecurringAgreementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Feature tests for fieldservice_sale_recurring_agreement module.
 *
 * Validates the full commercial-to-execution pipeline:
 *   Quote approved → RecurringAgreement → ServicePlan → ServicePlanVisit → ServiceJob
 */
class SaleRecurringAgreementTest extends TestCase
{
    use RefreshDatabase;

    private int $company = 99;

    private function makeSvc(): SaleRecurringAgreementService
    {
        return new SaleRecurringAgreementService(new FieldServiceSaleService());
    }

    private function makeQuoteWithRecurringLine(): Quote
    {
        $customer = Customer::factory()->create(['company_id' => $this->company]);

        $quote = Quote::factory()->create([
            'company_id'  => $this->company,
            'customer_id' => $customer->id,
            'status'      => 'accepted',
            'issue_date'  => now()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
        ]);

        QuoteItem::factory()->create([
            'company_id'               => $this->company,
            'quote_id'                 => $quote->id,
            'description'              => 'Monthly pest control',
            'quantity'                 => 12,
            'unit_price'               => 100,
            'field_service_tracking'   => QuoteItem::TRACKING_LINE,
            'service_tracking_type'    => 'monthly',
        ]);

        return $quote->fresh();
    }

    // ── createRecurringAgreement ──────────────────────────────────────────────

    public function test_creates_recurring_agreement_from_accepted_quote(): void
    {
        Event::fake();

        $quote = $this->makeQuoteWithRecurringLine();
        $svc   = $this->makeSvc();

        $agreement = $svc->createRecurringAgreement($quote);

        $this->assertDatabaseHas('service_agreements', [
            'id'               => $agreement->id,
            'company_id'       => $this->company,
            'customer_id'      => $quote->customer_id,
            'recurring_source' => 'sale',
            'status'           => 'active',
        ]);

        $this->assertEquals('sale', $agreement->recurring_source);
        $this->assertNotNull($agreement->sale_recurrence_terms);
        $this->assertEquals('monthly', $agreement->sale_recurrence_terms['frequency']);

        Event::assertDispatched(SaleRecurringAgreementCreated::class, function ($e) use ($quote, $agreement) {
            return $e->quote->id === $quote->id && $e->agreement->id === $agreement->id;
        });
    }

    // ── attachRecurringPlanToAgreement ────────────────────────────────────────

    public function test_attaches_recurring_plan_to_agreement(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);

        $plan = $svc->attachRecurringPlanToAgreement($quote, $agreement);

        $this->assertDatabaseHas('service_plans', [
            'id'                   => $plan->id,
            'company_id'           => $this->company,
            'agreement_id'         => $agreement->id,
            'originated_from_sale' => 1,
            'status'               => 'active',
        ]);

        $this->assertTrue((bool) $plan->originated_from_sale);
        $this->assertEquals($agreement->id, $plan->sale_agreement_id);

        Event::assertDispatched(SaleRecurringPlanGenerated::class, function ($e) use ($agreement, $plan) {
            return $e->agreement->id === $agreement->id && $e->plan->id === $plan->id;
        });

        Event::assertDispatched(SaleRecurringCoverageApplied::class, function ($e) use ($agreement, $plan) {
            return $e->agreement->id === $agreement->id && $e->plan->id === $plan->id;
        });
    }

    // ── projectVisits ─────────────────────────────────────────────────────────

    public function test_projects_correct_number_of_visits(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);
        $plan      = $svc->attachRecurringPlanToAgreement($quote, $agreement);

        $visits = $svc->projectVisits($plan, 3);

        $this->assertCount(3, $visits);

        foreach ($visits as $visit) {
            $this->assertDatabaseHas('service_plan_visits', [
                'id'              => $visit->id,
                'service_plan_id' => $plan->id,
                'sale_originated' => 1,
                'status'          => 'pending',
            ]);
            $this->assertEquals($agreement->id, $visit->sale_agreement_id);
        }

        Event::assertDispatchedTimes(SaleRecurringVisitProjected::class, 3);
    }

    public function test_projected_visits_advance_by_frequency(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);
        $plan      = $svc->attachRecurringPlanToAgreement($quote, $agreement, ['frequency' => 'monthly']);

        $visits = $svc->projectVisits($plan, 3);

        $dates = $visits->pluck('scheduled_date')->map(fn ($d) => $d->format('Y-m'));

        // Each visit should be in a different month
        $this->assertEquals(3, $dates->unique()->count());
    }

    // ── materializeVisit ──────────────────────────────────────────────────────

    public function test_materializes_visit_as_service_job(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);
        $plan      = $svc->attachRecurringPlanToAgreement($quote, $agreement);
        $visits    = $svc->projectVisits($plan, 1);
        $visit     = $visits->first();

        $job = $svc->materializeVisit($visit);

        $this->assertDatabaseHas('service_jobs', [
            'id'           => $job->id,
            'agreement_id' => $agreement->id,
        ]);

        $this->assertDatabaseHas('service_plan_visits', [
            'id'             => $visit->id,
            'service_job_id' => $job->id,
            'status'         => 'scheduled',
        ]);

        Event::assertDispatched(SaleRecurringVisitMaterialized::class, function ($e) use ($visit, $job) {
            return $e->visit->id === $visit->id && $e->job->id === $job->id;
        });
    }

    // ── runRecurringPipeline ──────────────────────────────────────────────────

    public function test_run_recurring_pipeline_end_to_end(): void
    {
        Event::fake();

        $quote = $this->makeQuoteWithRecurringLine();
        $svc   = $this->makeSvc();

        $result = $svc->runRecurringPipeline($quote, [
            'project_visits'         => true,
            'materialize_first_visit'=> true,
        ]);

        $this->assertInstanceOf(ServiceAgreement::class, $result['agreement']);
        $this->assertInstanceOf(ServicePlan::class, $result['plan']);
        $this->assertNotEmpty($result['visits']);
        $this->assertNotNull($result['firstJob']);

        $agreement = $result['agreement'];
        $plan      = $result['plan'];

        $this->assertEquals('sale', $agreement->recurring_source);
        $this->assertTrue((bool) $plan->originated_from_sale);
        $this->assertEquals($agreement->id, $plan->sale_agreement_id);

        // All visits are sale-originated
        foreach ($result['visits'] as $visit) {
            $this->assertTrue((bool) $visit->sale_originated);
            $this->assertEquals($agreement->id, $visit->sale_agreement_id);
        }

        // First visit is materialized
        $firstVisit = $result['visits']->first()->fresh();
        $this->assertNotNull($firstVisit->service_job_id);

        Event::assertDispatched(SaleRecurringAgreementCreated::class);
        Event::assertDispatched(SaleRecurringPlanGenerated::class);
        Event::assertDispatched(SaleRecurringVisitProjected::class);
        Event::assertDispatched(SaleRecurringVisitMaterialized::class);
    }

    public function test_run_pipeline_extends_existing_agreement(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);

        // Run pipeline with existing agreement (extension/renewal)
        $result = $svc->runRecurringPipeline($quote, [
            'agreement'      => $agreement,
            'project_visits' => false,
        ]);

        // Same agreement should have been updated
        $this->assertEquals($agreement->id, $result['agreement']->id);

        Event::assertDispatched(SaleRecurringAgreementUpdated::class, function ($e) use ($agreement, $quote) {
            return $e->agreement->id === $agreement->id && $e->renewalQuote->id === $quote->id;
        });
    }

    // ── Quote canonical helpers ───────────────────────────────────────────────

    public function test_quote_generated_agreement_returns_created_agreement(): void
    {
        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);

        $found = $quote->generatedAgreement();

        $this->assertNotNull($found);
        $this->assertEquals($agreement->id, $found->id);
    }

    public function test_quote_generated_recurring_plan_returns_sale_plan(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);
        $plan      = $svc->attachRecurringPlanToAgreement($quote, $agreement);

        $found = $quote->generatedRecurringPlan();

        $this->assertNotNull($found);
        $this->assertEquals($plan->id, $found->id);
    }

    public function test_quote_service_commitment_summary_reflects_pipeline(): void
    {
        Event::fake();

        $quote = $this->makeQuoteWithRecurringLine();
        $svc   = $this->makeSvc();

        $result = $svc->runRecurringPipeline($quote, ['project_visits' => true]);

        $summary = $quote->fresh()->serviceCommitmentSummary();

        $this->assertEquals($quote->id, $summary['quote_id']);
        $this->assertTrue($summary['has_field_work']);
        $this->assertTrue($summary['has_recurring']);
        $this->assertEquals($result['agreement']->id, $summary['agreement_id']);
        $this->assertEquals($result['plan']->id, $summary['plan_id']);
        $this->assertGreaterThan(0, $summary['projected_visits']);
    }

    // ── ServiceAgreement canonical helpers ────────────────────────────────────

    public function test_agreement_recurring_obligation_summary(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $result    = $svc->runRecurringPipeline($quote, ['project_visits' => true]);
        $agreement = $result['agreement'];

        $summary = $agreement->recurringObligationSummary();

        $this->assertEquals($agreement->id, $summary['agreement_id']);
        $this->assertEquals('sale', $summary['recurring_source']);
        $this->assertGreaterThan(0, $summary['projected_visits']);
    }

    public function test_agreement_commercial_coverage_source_returns_quote(): void
    {
        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);

        $source = $agreement->commercialCoverageSource();

        $this->assertNotNull($source);
        $this->assertEquals($quote->id, $source->id);
    }

    // ── ServicePlan canonical helpers ─────────────────────────────────────────

    public function test_plan_commercial_origin_summary(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);
        $plan      = $svc->attachRecurringPlanToAgreement($quote, $agreement);

        $summary = $plan->commercialOriginSummary();

        $this->assertTrue($summary['originated_from_sale']);
        $this->assertEquals($agreement->id, $summary['agreement_id']);
        $this->assertEquals($quote->id, $summary['originating_quote_id']);
    }

    // ── ServicePlanVisit canonical helpers ────────────────────────────────────

    public function test_visit_commercial_source_resolves_to_quote(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);
        $plan      = $svc->attachRecurringPlanToAgreement($quote, $agreement);
        $visits    = $svc->projectVisits($plan, 1);
        $visit     = $visits->first();

        $source = $visit->commercialSource();

        $this->assertNotNull($source);
        $this->assertEquals($quote->id, $source->id);
    }

    public function test_visit_sale_agreement_source_resolves_to_agreement(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);
        $plan      = $svc->attachRecurringPlanToAgreement($quote, $agreement);
        $visits    = $svc->projectVisits($plan, 1);

        $agreementFromVisit = $visits->first()->saleAgreementSource();

        $this->assertNotNull($agreementFromVisit);
        $this->assertEquals($agreement->id, $agreementFromVisit->id);
    }

    // ── Customer canonical helpers ────────────────────────────────────────────

    public function test_customer_recurring_coverage_from_sales(): void
    {
        Event::fake();

        $quote    = $this->makeQuoteWithRecurringLine();
        $svc      = $this->makeSvc();
        $svc->createRecurringAgreement($quote);

        $customer = Customer::find($quote->customer_id);
        $coverage = $customer->recurringCoverageFromSales();

        $this->assertCount(1, $coverage);
        $this->assertEquals('sale', $coverage->first()->recurring_source);
    }

    public function test_customer_upcoming_sold_recurring_visits(): void
    {
        Event::fake();

        $quote  = $this->makeQuoteWithRecurringLine();
        $svc    = $this->makeSvc();
        $result = $svc->runRecurringPipeline($quote, ['project_visits' => true]);

        $customer = Customer::find($quote->customer_id);
        $visits   = $customer->upcomingSoldRecurringVisits();

        $this->assertGreaterThan(0, $visits->count());

        foreach ($visits as $visit) {
            $this->assertTrue((bool) $visit->sale_originated);
            $this->assertEquals('pending', $visit->status);
        }
    }

    // ── Scheduling compatibility ──────────────────────────────────────────────

    public function test_projected_visits_are_schedulable_entities(): void
    {
        Event::fake();

        $quote   = $this->makeQuoteWithRecurringLine();
        $svc     = $this->makeSvc();
        $result  = $svc->runRecurringPipeline($quote, ['project_visits' => true]);
        $visit   = $result['visits']->first();

        $this->assertNotNull($visit->getScheduledStart());
        $this->assertEquals('pending', $visit->getSchedulableStatus());
        $this->assertEquals('recurring', $visit->visit_type);
    }

    // ── updateRecurringTermsFromSale ──────────────────────────────────────────

    public function test_update_recurring_terms_sets_renewal_quote_and_extends_coverage(): void
    {
        Event::fake();

        $quote     = $this->makeQuoteWithRecurringLine();
        $svc       = $this->makeSvc();
        $agreement = $svc->createRecurringAgreement($quote);

        // Create a renewal quote
        $renewal = Quote::factory()->create([
            'company_id'  => $this->company,
            'customer_id' => $quote->customer_id,
            'status'      => 'accepted',
            'valid_until' => now()->addYears(2)->toDateString(),
        ]);

        QuoteItem::factory()->create([
            'company_id'             => $this->company,
            'quote_id'               => $renewal->id,
            'field_service_tracking' => QuoteItem::TRACKING_LINE,
            'service_tracking_type'  => 'monthly',
            'quantity'               => 12,
        ]);

        $updated = $svc->updateRecurringTermsFromSale($agreement, $renewal->fresh());

        $this->assertEquals($renewal->id, $updated->renewal_quote_id);
        $this->assertNotNull($updated->sale_recurrence_terms);

        Event::assertDispatched(SaleRecurringAgreementUpdated::class);
    }
}
