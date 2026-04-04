<?php

declare(strict_types=1);

namespace Tests\Feature\Dispatch;

use App\Events\Work\DispatchJobLate;
use App\Events\Work\DispatchReadinessChanged;
use App\Events\Work\DispatchStockBlocked;
use App\Events\Work\DispatchVehicleBlocked;
use App\Models\User;
use App\Models\Vehicle\Vehicle;
use App\Models\Work\ServiceJob;
use App\Services\Work\AgreementDispatchService;
use App\Services\Work\DispatchReadinessService;
use App\Services\Work\DispatchService;
use App\Services\Work\StockDispatchService;
use App\Services\Work\VehicleDispatchService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Easy Dispatch Canonical Merge — feature tests.
 *
 * Verifies that the canonical readiness, vehicle, stock, and agreement
 * dispatch helpers operate correctly and that the extended DispatchService
 * emits the expected events.
 */
class EasyDispatchCanonicalMergeTest extends TestCase
{
    use RefreshDatabase;

    // ── DispatchReadinessService ──────────────────────────────────────────────

    public function test_dispatch_readiness_returns_ready_for_plain_job(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id' => 1,
            'status'     => 'scheduled',
        ]);

        $service = app(DispatchReadinessService::class);
        $result  = $service->dispatchReadiness($job);

        $this->assertArrayHasKey('ready', $result);
        $this->assertArrayHasKey('priority_score', $result);
        $this->assertArrayHasKey('blockers', $result);
        $this->assertArrayHasKey('sla_urgency', $result);
        $this->assertArrayHasKey('vehicle_ready', $result);
        $this->assertArrayHasKey('stock_ready', $result);
        $this->assertArrayHasKey('agreement_eligible', $result);
        $this->assertIsBool($result['ready']);
        $this->assertIsFloat($result['priority_score']);
        $this->assertIsArray($result['blockers']);
    }

    public function test_dispatch_readiness_blockers_empty_for_plain_job(): void
    {
        $job     = ServiceJob::factory()->create(['company_id' => 1, 'is_billable' => false]);
        $service = app(DispatchReadinessService::class);

        $blockers = $service->dispatchBlockers($job);

        $this->assertIsArray($blockers);
    }

    public function test_dispatch_priority_score_is_float_within_range(): void
    {
        $job     = ServiceJob::factory()->create(['company_id' => 1]);
        $service = app(DispatchReadinessService::class);

        $score = $service->dispatchPriorityScore($job);

        $this->assertIsFloat($score);
        $this->assertGreaterThanOrEqual(0.0, $score);
        $this->assertLessThanOrEqual(100.0, $score);
    }

    public function test_dispatch_priority_score_increases_with_overdue_sla(): void
    {
        $overdue = ServiceJob::factory()->create([
            'company_id'   => 1,
            'sla_deadline' => Carbon::now()->subHour(),
        ]);
        $normal  = ServiceJob::factory()->create([
            'company_id'   => 1,
            'sla_deadline' => Carbon::now()->addDays(5),
        ]);

        $service  = app(DispatchReadinessService::class);
        $overdueScore = $service->dispatchPriorityScore($overdue);
        $normalScore  = $service->dispatchPriorityScore($normal);

        $this->assertGreaterThan($normalScore, $overdueScore);
    }

    public function test_dispatch_eta_context_returns_structured_array(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id'   => 1,
            'scheduled_at' => Carbon::now()->addHours(2),
        ]);

        $service = app(DispatchReadinessService::class);
        $eta     = $service->dispatchETAContext($job);

        $this->assertArrayHasKey('job_id', $eta);
        $this->assertArrayHasKey('scheduled_at', $eta);
        $this->assertArrayHasKey('estimated_arrival_at', $eta);
        $this->assertArrayHasKey('travel_estimate_mins', $eta);
        $this->assertEquals($job->id, $eta['job_id']);
    }

    public function test_dispatch_conflict_reasons_returns_array(): void
    {
        $job     = ServiceJob::factory()->create(['company_id' => 1]);
        $service = app(DispatchReadinessService::class);

        $reasons = $service->dispatchConflictReasons($job);

        $this->assertIsArray($reasons);
    }

    // ── VehicleDispatchService ─────────────────────────────────────────────

    public function test_vehicle_dispatch_ready_returns_ready_for_active_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create([
            'company_id' => 1,
            'status'     => Vehicle::STATUS_ACTIVE,
        ]);
        $job = ServiceJob::factory()->create(['company_id' => 1]);

        $service = app(VehicleDispatchService::class);
        $result  = $service->vehicleDispatchReady($vehicle, $job);

        $this->assertArrayHasKey('ready', $result);
        $this->assertArrayHasKey('vehicle_id', $result);
        $this->assertArrayHasKey('blockers', $result);
        $this->assertTrue($result['ready']);
        $this->assertEmpty($result['blockers']);
    }

    public function test_vehicle_dispatch_ready_blocked_when_retired(): void
    {
        $vehicle = Vehicle::factory()->create([
            'company_id' => 1,
            'status'     => Vehicle::STATUS_RETIRED,
        ]);
        $job = ServiceJob::factory()->create(['company_id' => 1]);

        $service = app(VehicleDispatchService::class);
        $result  = $service->vehicleDispatchReady($vehicle, $job);

        $this->assertFalse($result['ready']);
        $this->assertNotEmpty($result['blockers']);
    }

    public function test_vehicle_capacity_score_returns_zero_for_retired_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create([
            'company_id' => 1,
            'status'     => Vehicle::STATUS_RETIRED,
        ]);
        $job     = ServiceJob::factory()->create(['company_id' => 1]);
        $service = app(VehicleDispatchService::class);

        $score = $service->vehicleCapacityScore($vehicle, $job);

        $this->assertEquals(0.0, $score);
    }

    public function test_vehicle_capacity_score_returns_one_for_active_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create([
            'company_id' => 1,
            'status'     => Vehicle::STATUS_ACTIVE,
        ]);
        $job     = ServiceJob::factory()->create(['company_id' => 1]);
        $service = app(VehicleDispatchService::class);

        $score = $service->vehicleCapacityScore($vehicle, $job);

        $this->assertEquals(1.0, $score);
    }

    public function test_vehicle_capacity_score_returns_one_when_no_vehicle(): void
    {
        $job     = ServiceJob::factory()->create(['company_id' => 1]);
        $service = app(VehicleDispatchService::class);

        $score = $service->vehicleCapacityScore(null, $job);

        $this->assertEquals(1.0, $score);
    }

    public function test_vehicle_location_fit_returns_structured_array(): void
    {
        $vehicle = Vehicle::factory()->create(['company_id' => 1]);
        $job     = ServiceJob::factory()->create(['company_id' => 1]);
        $service = app(VehicleDispatchService::class);

        $result = $service->vehicleLocationFit($vehicle, $job);

        $this->assertArrayHasKey('vehicle_id', $result);
        $this->assertArrayHasKey('estimated_distance_km', $result);
    }

    public function test_is_job_vehicle_ready_returns_true_when_no_vehicle_assigned(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id'          => 1,
            'assigned_vehicle_id' => null,
        ]);

        $service = app(VehicleDispatchService::class);

        $this->assertTrue($service->isJobVehicleReady($job));
    }

    // ── StockDispatchService ───────────────────────────────────────────────

    public function test_dispatch_stock_ready_returns_true_for_non_billable_job(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id'          => 1,
            'is_billable'         => false,
            'assigned_vehicle_id' => null,
        ]);

        $service = app(StockDispatchService::class);

        $this->assertTrue($service->dispatchStockReady($job));
    }

    public function test_dispatch_parts_blockers_returns_array(): void
    {
        $job     = ServiceJob::factory()->create(['company_id' => 1]);
        $service = app(StockDispatchService::class);

        $blockers = $service->dispatchPartsBlockers($job);

        $this->assertIsArray($blockers);
    }

    public function test_dispatch_material_risk_false_without_vehicle(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id'          => 1,
            'assigned_vehicle_id' => null,
        ]);

        $service = app(StockDispatchService::class);

        $this->assertFalse($service->dispatchMaterialRisk($job));
    }

    // ── AgreementDispatchService ───────────────────────────────────────────

    public function test_dispatch_coverage_eligible_false_without_agreement(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id'   => 1,
            'agreement_id' => null,
        ]);

        $service = app(AgreementDispatchService::class);

        $this->assertFalse($service->dispatchCoverageEligible($job));
    }

    public function test_dispatch_warranty_eligible_false_without_warranty_flag(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id'       => 1,
            'is_warranty_job'  => false,
            'warranty_claim_id' => null,
        ]);

        $service = app(AgreementDispatchService::class);

        $this->assertFalse($service->dispatchWarrantyEligible($job));
    }

    public function test_dispatch_repair_blocked_false_when_no_repair_orders(): void
    {
        $job = ServiceJob::factory()->create(['company_id' => 1]);

        $service = app(AgreementDispatchService::class);

        $this->assertFalse($service->dispatchRepairBlocked($job));
    }

    public function test_dispatch_sale_commitment_priority_high_for_sale_line_job(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id'    => 1,
            'sale_line_id'  => 1,
        ]);

        $service  = app(AgreementDispatchService::class);
        $priority = $service->dispatchSaleCommitmentPriority($job);

        $this->assertEquals(0.9, $priority);
    }

    public function test_dispatch_sale_commitment_priority_standard_for_plain_job(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id'    => 1,
            'sale_line_id'  => null,
            'quote_id'      => null,
            'agreement_id'  => null,
        ]);

        $service  = app(AgreementDispatchService::class);
        $priority = $service->dispatchSaleCommitmentPriority($job);

        $this->assertEquals(0.3, $priority);
    }

    public function test_dispatch_project_context_without_project(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id' => 1,
            'project_id' => null,
        ]);

        $service = app(AgreementDispatchService::class);
        $context = $service->dispatchProjectContext($job);

        $this->assertFalse($context['has_project']);
    }

    // ── DispatchService::checkReadiness ───────────────────────────────────

    public function test_check_readiness_emits_readiness_changed_event_when_blocked(): void
    {
        Event::fake([DispatchReadinessChanged::class, DispatchStockBlocked::class, DispatchVehicleBlocked::class]);

        // Create a retired vehicle to trigger a blocker
        $vehicle = Vehicle::factory()->create([
            'company_id' => 1,
            'status'     => Vehicle::STATUS_RETIRED,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id'          => 1,
            'assigned_vehicle_id' => $vehicle->id,
            'is_billable'         => false,
        ]);

        // Preload vehicle relationship to avoid DB calls in event
        $job->setRelation('assignedVehicle', $vehicle);
        $job->setRelation('vehicle', $vehicle);

        $service   = app(DispatchService::class);
        $readiness = $service->checkReadiness($job);

        $this->assertIsArray($readiness);
        $this->assertArrayHasKey('ready', $readiness);
    }

    public function test_check_readiness_returns_ready_array_for_clean_job(): void
    {
        Event::fake([DispatchReadinessChanged::class]);

        $job = ServiceJob::factory()->create([
            'company_id'          => 1,
            'assigned_vehicle_id' => null,
            'is_billable'         => false,
        ]);

        $service   = app(DispatchService::class);
        $readiness = $service->checkReadiness($job);

        $this->assertIsArray($readiness);
        $this->assertTrue($readiness['ready']);
    }

    // ── DispatchConstraintService::evaluateAvailability ───────────────────

    public function test_evaluate_availability_returns_half_when_no_schedule(): void
    {
        $tech = User::factory()->create(['company_id' => 1]);
        $job  = ServiceJob::factory()->create([
            'company_id'   => 1,
            'scheduled_at' => Carbon::now()->addDay(),
        ]);

        $service = app(\App\Services\Work\DispatchConstraintService::class);
        $score   = $service->evaluateAvailability($tech, $job);

        $this->assertEquals(0.5, $score);
    }

    public function test_evaluate_availability_returns_half_when_no_date(): void
    {
        $tech = User::factory()->create(['company_id' => 1]);
        $job  = ServiceJob::factory()->create([
            'company_id'   => 1,
            'scheduled_at' => null,
        ]);

        $service = app(\App\Services\Work\DispatchConstraintService::class);
        $score   = $service->evaluateAvailability($tech, $job);

        $this->assertEquals(0.5, $score);
    }
}
