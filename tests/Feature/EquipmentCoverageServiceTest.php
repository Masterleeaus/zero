<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\Work\AgreementEquipmentCoverageCreated;
use App\Events\Work\AgreementEquipmentCoverageExtended;
use App\Events\Work\RecurringEquipmentServiceCreated;
use App\Models\Equipment\Equipment;
use App\Models\Equipment\InstalledEquipment;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServicePlan;
use App\Services\Work\EquipmentCoverageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EquipmentCoverageServiceTest extends TestCase
{
    use RefreshDatabase;

    private EquipmentCoverageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EquipmentCoverageService();
    }

    // ── Coverage activation ─────────────────────────────────────────────────

    public function test_activate_coverage_links_installed_equipment_to_agreement(): void
    {
        Event::fake();

        $agreement = ServiceAgreement::factory()->create(['company_id' => 1]);
        $equipment = Equipment::factory()->create(['company_id' => 1]);
        $installed = InstalledEquipment::factory()->create([
            'company_id'   => 1,
            'equipment_id' => $equipment->id,
        ]);

        $result = $this->service->activateCoverageForEquipment($agreement, $installed, [
            'coverage_start_date' => '2026-04-01',
            'coverage_end_date'   => '2027-04-01',
        ]);

        $this->assertEquals($agreement->id, $result->agreement_id);
        $this->assertEquals('2026-04-01', $result->coverage_start_date->toDateString());
        $this->assertEquals('2027-04-01', $result->coverage_end_date->toDateString());
        $this->assertNotNull($result->coverage_activated_at);

        Event::assertDispatched(AgreementEquipmentCoverageCreated::class, static function ($event) use ($agreement, $installed) {
            return $event->agreement->id === $agreement->id
                && $event->installedEquipment->id === $installed->id;
        });
    }

    public function test_activate_coverage_flags_agreement_has_equipment_coverage(): void
    {
        Event::fake();

        $agreement = ServiceAgreement::factory()->create(['company_id' => 1, 'has_equipment_coverage' => false]);
        $installed = InstalledEquipment::factory()->create(['company_id' => 1]);

        $this->service->activateCoverageForEquipment($agreement, $installed);

        $this->assertTrue((bool) $agreement->fresh()->has_equipment_coverage);
    }

    public function test_reactivating_existing_coverage_fires_extended_event(): void
    {
        Event::fake();

        $agreement = ServiceAgreement::factory()->create(['company_id' => 1]);
        $installed = InstalledEquipment::factory()->create([
            'company_id'            => 1,
            'agreement_id'          => $agreement->id,
            'coverage_activated_at' => now()->subMonth(),
        ]);

        // Re-activate (already has coverage_activated_at)
        $this->service->activateCoverageForEquipment($agreement, $installed, [
            'coverage_end_date' => '2028-01-01',
        ]);

        Event::assertDispatched(AgreementEquipmentCoverageExtended::class);
        Event::assertNotDispatched(AgreementEquipmentCoverageCreated::class);
    }

    public function test_activate_coverage_from_equipment_creates_installed_record_when_none_exists(): void
    {
        Event::fake();

        $agreement = ServiceAgreement::factory()->create(['company_id' => 1]);
        $equipment = Equipment::factory()->create(['company_id' => 1]);

        // No existing InstalledEquipment
        $installed = $this->service->activateCoverageFromEquipment($agreement, $equipment);

        $this->assertInstanceOf(InstalledEquipment::class, $installed);
        $this->assertEquals($agreement->id, $installed->agreement_id);
        $this->assertDatabaseHas('installed_equipment', [
            'equipment_id' => $equipment->id,
            'agreement_id' => $agreement->id,
            'company_id'   => 1,
        ]);
    }

    // ── Coverage queries ────────────────────────────────────────────────────

    public function test_covered_equipment_for_agreement_returns_active_installations(): void
    {
        $agreement = ServiceAgreement::factory()->create(['company_id' => 1]);

        InstalledEquipment::factory()->create([
            'company_id'            => 1,
            'agreement_id'          => $agreement->id,
            'status'                => 'active',
            'coverage_activated_at' => now(),
        ]);

        InstalledEquipment::factory()->create([
            'company_id'   => 1,
            'agreement_id' => $agreement->id,
            'status'       => 'removed', // excluded
        ]);

        InstalledEquipment::factory()->create([
            'company_id'   => 1,
            'agreement_id' => null, // different agreement
            'status'       => 'active',
        ]);

        $covered = $this->service->coveredEquipmentForAgreement($agreement);

        $this->assertCount(1, $covered);
    }

    // ── Coverage timeline ───────────────────────────────────────────────────

    public function test_coverage_timeline_returns_structured_data(): void
    {
        $agreement = ServiceAgreement::factory()->create(['company_id' => 1, 'has_equipment_coverage' => true]);
        $equipment = Equipment::factory()->create(['company_id' => 1, 'name' => 'Test Pump']);
        InstalledEquipment::factory()->create([
            'company_id'            => 1,
            'equipment_id'          => $equipment->id,
            'agreement_id'          => $agreement->id,
            'status'                => 'active',
            'coverage_activated_at' => now(),
            'coverage_start_date'   => '2026-01-01',
        ]);

        $timeline = $this->service->coverageTimeline($agreement);

        $this->assertEquals($agreement->id, $timeline['agreement_id']);
        $this->assertTrue($timeline['has_equipment_coverage']);
        $this->assertCount(1, $timeline['equipment']);
        $this->assertEquals('Test Pump', $timeline['equipment'][0]['equipment_name']);
    }

    // ── Recurring equipment plan ────────────────────────────────────────────

    public function test_create_equipment_recurring_plan_returns_service_plan(): void
    {
        Event::fake();

        $agreement = ServiceAgreement::factory()->create(['company_id' => 1]);
        $equipment = Equipment::factory()->create(['company_id' => 1, 'name' => 'HVAC Unit']);
        $installed = InstalledEquipment::factory()->create([
            'company_id'   => 1,
            'equipment_id' => $equipment->id,
            'agreement_id' => $agreement->id,
        ]);

        $plan = $this->service->createEquipmentRecurringPlan($agreement, $installed);

        $this->assertInstanceOf(ServicePlan::class, $plan);
        $this->assertEquals($agreement->id, $plan->agreement_id);
        $this->assertEquals('maintenance', $plan->recurrence_type);
        $this->assertContains($installed->id, $plan->equipment_scope);

        Event::assertDispatched(RecurringEquipmentServiceCreated::class, static function ($event) use ($plan, $installed) {
            return $event->plan->id === $plan->id
                && $event->installedEquipment->id === $installed->id;
        });
    }

    public function test_create_equipment_recurring_plan_increments_recurring_plan_count(): void
    {
        Event::fake();

        $agreement = ServiceAgreement::factory()->create(['company_id' => 1, 'recurring_plan_count' => 0]);
        $installed = InstalledEquipment::factory()->create(['company_id' => 1]);

        $this->service->createEquipmentRecurringPlan($agreement, $installed);

        $this->assertEquals(1, $agreement->fresh()->recurring_plan_count);
    }

    // ── Model helpers ───────────────────────────────────────────────────────

    public function test_installed_equipment_has_coverage_agreement_helper(): void
    {
        $installed = InstalledEquipment::factory()->create([
            'company_id'            => 1,
            'agreement_id'          => 99,
            'coverage_activated_at' => now(),
            'coverage_end_date'     => null,
        ]);

        $this->assertTrue($installed->hasCoverageAgreement());
    }

    public function test_installed_equipment_has_no_coverage_when_agreement_null(): void
    {
        $installed = InstalledEquipment::factory()->create([
            'company_id'   => 1,
            'agreement_id' => null,
        ]);

        $this->assertFalse($installed->hasCoverageAgreement());
    }

    public function test_service_agreement_covered_equipment_collection(): void
    {
        $agreement = ServiceAgreement::factory()->create(['company_id' => 1]);

        InstalledEquipment::factory()->create([
            'company_id'            => 1,
            'agreement_id'          => $agreement->id,
            'status'                => 'active',
            'coverage_activated_at' => now(),
            'coverage_end_date'     => null,
        ]);

        $covered = $agreement->coveredEquipment();
        $this->assertCount(1, $covered);
    }

    public function test_service_agreement_recurring_coverage_summary(): void
    {
        $agreement = ServiceAgreement::factory()->create([
            'company_id'             => 1,
            'has_equipment_coverage' => true,
        ]);

        $summary = $agreement->recurringCoverageSummary();

        $this->assertArrayHasKey('agreement_id', $summary);
        $this->assertArrayHasKey('has_equipment_coverage', $summary);
        $this->assertArrayHasKey('active_plan_count', $summary);
        $this->assertArrayHasKey('covered_equipment_count', $summary);
        $this->assertTrue($summary['has_equipment_coverage']);
    }
}
