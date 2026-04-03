<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\Work\RecurringPlanGenerated;
use App\Events\Work\RecurringSaleCreated;
use App\Events\Work\RecurringVisitMaterialized;
use App\Models\Money\Quote;
use App\Models\Money\QuoteItem;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlan;
use App\Models\Work\ServicePlanVisit;
use App\Services\Work\RecurringSaleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RecurringSaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecurringSaleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RecurringSaleService();
    }

    // ── Recurring line detection ────────────────────────────────────────────

    public function test_recurring_lines_from_quote_detects_maintenance_type(): void
    {
        $quote = Quote::factory()->create(['company_id' => 1]);

        // Recurring line
        QuoteItem::factory()->create([
            'company_id'             => 1,
            'quote_id'               => $quote->id,
            'field_service_tracking' => QuoteItem::TRACKING_SALE,
            'service_tracking_type'  => 'recurring_maintenance',
        ]);

        // Non-recurring line
        QuoteItem::factory()->create([
            'company_id'             => 1,
            'quote_id'               => $quote->id,
            'field_service_tracking' => 'no',
        ]);

        $lines = $this->service->recurringLinesFromQuote($quote);

        $this->assertCount(1, $lines);
    }

    public function test_recurring_lines_from_quote_detects_inspection_type(): void
    {
        $quote = Quote::factory()->create(['company_id' => 1]);

        QuoteItem::factory()->create([
            'company_id'             => 1,
            'quote_id'               => $quote->id,
            'field_service_tracking' => QuoteItem::TRACKING_LINE,
            'service_tracking_type'  => 'annual_inspection',
        ]);

        $lines = $this->service->recurringLinesFromQuote($quote);
        $this->assertCount(1, $lines);
    }

    // ── Plan creation ───────────────────────────────────────────────────────

    public function test_create_recurring_plan_from_sale_creates_service_plan(): void
    {
        Event::fake();

        $quote     = Quote::factory()->create(['company_id' => 1]);
        $agreement = ServiceAgreement::factory()->create(['company_id' => 1]);

        $plan = $this->service->createRecurringPlanFromSale($quote, $agreement, [
            'recurrence_type' => RecurringSaleService::RECURRENCE_MAINTENANCE,
            'frequency'       => 'monthly',
        ]);

        $this->assertInstanceOf(ServicePlan::class, $plan);
        $this->assertEquals($agreement->id, $plan->agreement_id);
        $this->assertEquals($quote->id, $plan->origin_quote_id);
        $this->assertEquals('maintenance', $plan->recurrence_type);
        $this->assertTrue((bool) $plan->auto_generate_visits);

        Event::assertDispatched(RecurringSaleCreated::class, static fn ($e) => $e->quote->id === $quote->id);
        Event::assertDispatched(RecurringPlanGenerated::class, static fn ($e) => $e->plan->id === $plan->id);
    }

    public function test_create_recurring_plan_increments_recurring_plan_count(): void
    {
        Event::fake();

        $quote     = Quote::factory()->create(['company_id' => 1]);
        $agreement = ServiceAgreement::factory()->create(['company_id' => 1, 'recurring_plan_count' => 2]);

        $this->service->createRecurringPlanFromSale($quote, $agreement);

        $this->assertEquals(3, $agreement->fresh()->recurring_plan_count);
    }

    // ── Visit generation ────────────────────────────────────────────────────

    public function test_generate_visits_for_plan_creates_visits_in_range(): void
    {
        $plan = ServicePlan::factory()->create([
            'company_id'     => 1,
            'frequency'      => 'monthly',
            'recurrence_type' => 'maintenance',
            'starts_on'      => now()->toDateString(),
        ]);

        $from  = Carbon::now();
        $until = Carbon::now()->addMonths(3);

        $visits = $this->service->generateVisitsForPlan($plan, $from, $until);

        // monthly for 3 months = 3 visits (current month + 2 more)
        $this->assertGreaterThanOrEqual(3, $visits->count());
        $this->assertLessThanOrEqual(4, $visits->count());

        foreach ($visits as $visit) {
            $this->assertEquals('maintenance', $visit->visit_type);
            $this->assertEquals('agreement', $visit->coverage_source);
            $this->assertEquals('pending', $visit->status);
        }
    }

    public function test_generate_visits_skips_duplicate_dates(): void
    {
        $plan = ServicePlan::factory()->create([
            'company_id'  => 1,
            'frequency'   => 'monthly',
        ]);

        $from  = Carbon::now();
        $until = Carbon::now()->addMonth();

        // First generation
        $first = $this->service->generateVisitsForPlan($plan, $from, $until);

        // Second generation — should produce no duplicates
        $second = $this->service->generateVisitsForPlan($plan, $from, $until);

        $this->assertCount(0, $second);
    }

    public function test_generate_visits_weekly_frequency(): void
    {
        $plan = ServicePlan::factory()->create([
            'company_id' => 1,
            'frequency'  => 'weekly',
        ]);

        $from  = Carbon::now();
        $until = Carbon::now()->addWeeks(4);

        $visits = $this->service->generateVisitsForPlan($plan, $from, $until);

        $this->assertCount(4, $visits);
    }

    // ── Visit materialization ───────────────────────────────────────────────

    public function test_materialize_visit_to_job_creates_service_job(): void
    {
        Event::fake();

        $agreement = ServiceAgreement::factory()->create(['company_id' => 1]);
        $plan      = ServicePlan::factory()->create([
            'company_id'   => 1,
            'agreement_id' => $agreement->id,
            'customer_id'  => $agreement->customer_id,
        ]);
        $visit = ServicePlanVisit::factory()->create([
            'company_id'      => 1,
            'service_plan_id' => $plan->id,
            'status'          => 'pending',
        ]);

        $job = $this->service->materializeVisitToJob($visit);

        $this->assertInstanceOf(ServiceJob::class, $job);
        $this->assertEquals('scheduled', $visit->fresh()->status);
        $this->assertEquals($job->id, $visit->fresh()->service_job_id);

        Event::assertDispatched(RecurringVisitMaterialized::class);
    }

    public function test_materialize_visit_returns_existing_job_if_already_linked(): void
    {
        $existingJob = ServiceJob::factory()->create(['company_id' => 1]);
        $visit = ServicePlanVisit::factory()->create([
            'company_id'     => 1,
            'service_job_id' => $existingJob->id,
            'status'         => 'scheduled',
        ]);

        $job = $this->service->materializeVisitToJob($visit);

        $this->assertEquals($existingJob->id, $job->id);
    }

    // ── Regenerate pipeline ─────────────────────────────────────────────────

    public function test_regenerate_plans_from_agreement_update_cancels_and_recreates_visits(): void
    {
        $agreement = ServiceAgreement::factory()->create(['company_id' => 1]);
        $plan      = ServicePlan::factory()->create([
            'company_id'   => 1,
            'agreement_id' => $agreement->id,
            'status'       => 'active',
            'frequency'    => 'monthly',
        ]);

        // Create some pending future visits
        ServicePlanVisit::factory()->count(3)->create([
            'company_id'      => 1,
            'service_plan_id' => $plan->id,
            'status'          => 'pending',
            'scheduled_date'  => now()->addDays(10)->toDateString(),
        ]);

        $results = $this->service->regeneratePlansFromAgreementUpdate($agreement);

        $this->assertCount(1, $results);
        $this->assertEquals(3, $results[0]['cancelled']);
        $this->assertGreaterThan(0, $results[0]['created']);
    }

    // ── Full pipeline ───────────────────────────────────────────────────────

    public function test_run_recurring_pipeline_creates_plans_and_visits(): void
    {
        Event::fake();

        $quote = Quote::factory()->create([
            'company_id' => 1,
            'issue_date' => now()->toDateString(),
        ]);

        QuoteItem::factory()->create([
            'company_id'             => 1,
            'quote_id'               => $quote->id,
            'field_service_tracking' => QuoteItem::TRACKING_SALE,
            'service_tracking_type'  => 'recurring_maintenance',
        ]);

        $agreement = ServiceAgreement::factory()->create(['company_id' => 1]);

        $result = $this->service->runRecurringPipeline(
            $quote,
            $agreement,
            Carbon::now()->addMonths(2)
        );

        $this->assertNotEmpty($result['plans']);
        $this->assertNotEmpty($result['visits']);
        $this->assertInstanceOf(ServicePlan::class, $result['plans']->first());
        $this->assertInstanceOf(ServicePlanVisit::class, $result['visits']->first());
    }

    // ── Model helpers ───────────────────────────────────────────────────────

    public function test_service_plan_originating_sale_resolves_via_origin_quote_id(): void
    {
        $quote = Quote::factory()->create(['company_id' => 1]);
        $plan  = ServicePlan::factory()->create([
            'company_id'      => 1,
            'origin_quote_id' => $quote->id,
        ]);

        $this->assertEquals($quote->id, $plan->originatingSale()->id);
    }

    public function test_service_plan_recurring_coverage_scope_returns_summary(): void
    {
        $plan = ServicePlan::factory()->create([
            'company_id'           => 1,
            'recurrence_type'      => 'inspection',
            'frequency'            => 'monthly',
            'auto_generate_visits' => true,
            'equipment_scope'      => [1, 2],
        ]);

        $scope = $plan->recurringCoverageScope();

        $this->assertEquals('inspection', $scope['recurrence_type']);
        $this->assertEquals(2, $scope['equipment_count']);
        $this->assertTrue($scope['auto_generate_visits']);
    }

    public function test_service_plan_visit_coverage_source_defaults_to_manual(): void
    {
        $visit = ServicePlanVisit::factory()->create([
            'company_id'      => 1,
            'coverage_source' => null,
        ]);

        $this->assertEquals('manual', $visit->coverageSource());
    }

    public function test_service_plan_visit_equipment_context_returns_null_when_no_equipment(): void
    {
        $visit = ServicePlanVisit::factory()->create([
            'company_id'             => 1,
            'installed_equipment_id' => null,
        ]);

        $this->assertNull($visit->equipmentContext());
    }
}
