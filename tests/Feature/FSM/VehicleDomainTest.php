<?php

declare(strict_types=1);

namespace Tests\Feature\FSM;

use App\Events\Work\VehicleAssignedToJob;
use App\Events\Work\VehicleLocationUpdated;
use App\Events\Work\VehicleRouteReady;
use App\Events\Work\VehicleStockConsumed;
use App\Events\Work\VehicleStockReserved;
use App\Models\User;
use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleAssignment;
use App\Models\Vehicle\VehicleStock;
use App\Models\Work\ServiceJob;
use App\Models\Work\Shift;
use App\Services\FSM\VehicleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class VehicleDomainTest extends TestCase
{
    use RefreshDatabase;

    // ── Vehicle CRUD ──────────────────────────────────────────────────────────

    public function test_vehicle_index_scoped_to_company(): void
    {
        $user  = User::factory()->create(['company_id' => 1]);
        $other = User::factory()->create(['company_id' => 2]);

        Vehicle::factory()->create(['company_id' => 1, 'name' => 'Van A']);
        Vehicle::factory()->create(['company_id' => 2, 'name' => 'Van B']);

        $resp = $this->actingAs($user)->get(route('dashboard.work.vehicles.index'));
        $resp->assertStatus(200);
        $this->assertCount(1, $resp->viewData('vehicles'));
    }

    public function test_vehicle_can_be_created_via_post(): void
    {
        $user = User::factory()->create(['company_id' => 1]);

        $resp = $this->actingAs($user)->post(route('dashboard.work.vehicles.store'), [
            'name'         => 'Test Van',
            'vehicle_type' => 'van',
            'registration' => 'TST001',
        ]);

        $resp->assertRedirect();
        $this->assertDatabaseHas('vehicles', [
            'company_id'   => 1,
            'name'         => 'Test Van',
            'registration' => 'TST001',
        ]);
    }

    public function test_vehicle_can_be_updated(): void
    {
        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create(['company_id' => 1, 'name' => 'Old Name']);

        $resp = $this->actingAs($user)->put(
            route('dashboard.work.vehicles.update', $vehicle),
            ['name' => 'New Name', 'vehicle_type' => 'truck']
        );

        $resp->assertRedirect();
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'name' => 'New Name']);
    }

    // ── Capability tags ───────────────────────────────────────────────────────

    public function test_vehicle_has_capabilities_check(): void
    {
        $vehicle = Vehicle::factory()->create([
            'company_id'      => 1,
            'capability_tags' => ['ladder', 'chemical_safe'],
        ]);

        $this->assertTrue($vehicle->hasCapabilities(['ladder']));
        $this->assertTrue($vehicle->hasCapabilities(['ladder', 'chemical_safe']));
        $this->assertFalse($vehicle->hasCapabilities(['equipment_transport']));
    }

    // ── Job assignment ────────────────────────────────────────────────────────

    public function test_assign_vehicle_to_job_emits_event(): void
    {
        Event::fake([VehicleAssignedToJob::class]);

        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create(['company_id' => 1]);
        $job     = ServiceJob::factory(['company_id' => 1, 'site_id' => null])->create();

        $service = new VehicleService();
        $this->actingAs($user);

        $assignment = $service->assignVehicleToJob($vehicle, $job);

        $this->assertInstanceOf(VehicleAssignment::class, $assignment);
        $this->assertDatabaseHas('service_jobs', [
            'id'                  => $job->id,
            'assigned_vehicle_id' => $vehicle->id,
        ]);
        $this->assertDatabaseHas('vehicles', [
            'id'     => $vehicle->id,
            'status' => Vehicle::STATUS_IN_USE,
        ]);

        Event::assertDispatched(VehicleAssignedToJob::class);
    }

    public function test_release_vehicle_from_job(): void
    {
        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create(['company_id' => 1, 'status' => Vehicle::STATUS_IN_USE]);
        $job     = ServiceJob::factory(['company_id' => 1, 'site_id' => null])->create([
            'assigned_vehicle_id' => $vehicle->id,
        ]);
        VehicleAssignment::create([
            'company_id'      => 1,
            'vehicle_id'      => $vehicle->id,
            'assignable_type' => VehicleAssignment::ENTITY_SERVICE_JOB,
            'assignable_id'   => $job->id,
            'started_at'      => now(),
        ]);

        $service = new VehicleService();
        $this->actingAs($user);
        $service->releaseVehicleFromJob($job);

        $this->assertDatabaseHas('service_jobs', [
            'id'                  => $job->id,
            'assigned_vehicle_id' => null,
        ]);
        $this->assertDatabaseHas('vehicles', [
            'id'     => $vehicle->id,
            'status' => Vehicle::STATUS_ACTIVE,
        ]);
    }

    // ── Stock ─────────────────────────────────────────────────────────────────

    public function test_reserve_stock_for_job_emits_event(): void
    {
        Event::fake([VehicleStockReserved::class]);

        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create(['company_id' => 1]);
        $job     = ServiceJob::factory(['company_id' => 1, 'site_id' => null])->create();

        VehicleStock::create([
            'company_id' => 1,
            'vehicle_id' => $vehicle->id,
            'item_name'  => 'Filter',
            'sku'        => 'FLT-001',
            'quantity'   => 10,
            'status'     => VehicleStock::STATUS_AVAILABLE,
        ]);

        $service = new VehicleService();
        $this->actingAs($user);
        $service->reserveStockForJob($vehicle, $job, [['sku' => 'FLT-001', 'quantity' => 2]]);

        Event::assertDispatched(VehicleStockReserved::class);
        $this->assertDatabaseHas('vehicle_stock', [
            'vehicle_id'        => $vehicle->id,
            'sku'               => 'FLT-001',
            'quantity_reserved' => 2,
            'status'            => VehicleStock::STATUS_RESERVED,
        ]);
    }

    public function test_consume_stock_on_job_emits_event(): void
    {
        Event::fake([VehicleStockConsumed::class]);

        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create(['company_id' => 1]);
        $job     = ServiceJob::factory(['company_id' => 1, 'site_id' => null])->create();

        VehicleStock::create([
            'company_id' => 1,
            'vehicle_id' => $vehicle->id,
            'item_name'  => 'Filter',
            'sku'        => 'FLT-001',
            'quantity'   => 10,
            'status'     => VehicleStock::STATUS_AVAILABLE,
        ]);

        $service = new VehicleService();
        $this->actingAs($user);
        $service->consumeStockOnJob($vehicle, $job, [['sku' => 'FLT-001', 'quantity' => 3]]);

        Event::assertDispatched(VehicleStockConsumed::class);
        $this->assertDatabaseHas('vehicle_stock', [
            'vehicle_id'       => $vehicle->id,
            'sku'              => 'FLT-001',
            'quantity'         => 7,
            'quantity_consumed' => 3,
        ]);
    }

    // ── Location ──────────────────────────────────────────────────────────────

    public function test_record_location_snapshot_emits_event(): void
    {
        Event::fake([VehicleLocationUpdated::class]);

        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create(['company_id' => 1]);

        $service  = new VehicleService();
        $this->actingAs($user);
        $snapshot = $service->recordLocationSnapshot($vehicle, [
            'lat'    => -33.8688,
            'lng'    => 151.2093,
            'source' => 'mobile',
        ]);

        Event::assertDispatched(VehicleLocationUpdated::class);
        $this->assertDatabaseHas('vehicle_location_snapshots', [
            'vehicle_id' => $vehicle->id,
            'source'     => 'mobile',
        ]);

        $this->assertEquals([-33.8688, 151.2093], $snapshot->coordinates());
    }

    // ── Compatibility check ───────────────────────────────────────────────────

    public function test_compatibility_check_passes_for_capable_vehicle(): void
    {
        Event::fake([VehicleRouteReady::class]);

        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create([
            'company_id'      => 1,
            'capability_tags' => ['ladder'],
            'status'          => Vehicle::STATUS_ACTIVE,
        ]);
        $job = ServiceJob::factory(['company_id' => 1, 'site_id' => null])->create([
            'required_vehicle_type' => 'ladder',
        ]);

        $service = new VehicleService();
        $this->actingAs($user);
        $result = $service->checkJobCompatibility($vehicle, $job);

        $this->assertTrue($result['vehicle_equipment_ready']);
        $this->assertTrue($result['vehicle_capacity_ok']);
        $this->assertEmpty($result['blockers']);

        Event::assertDispatched(VehicleRouteReady::class);
    }

    public function test_compatibility_check_fails_for_incapable_vehicle(): void
    {
        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create([
            'company_id'      => 1,
            'capability_tags' => [],
            'status'          => Vehicle::STATUS_ACTIVE,
        ]);
        $job = ServiceJob::factory(['company_id' => 1, 'site_id' => null])->create([
            'required_vehicle_type' => 'ladder',
        ]);

        $service = new VehicleService();
        $this->actingAs($user);
        $result = $service->checkJobCompatibility($vehicle, $job);

        $this->assertFalse($result['vehicle_equipment_ready']);
        $this->assertFalse($result['vehicle_route_ready']);
        $this->assertContains('vehicle_capability_mismatch', $result['blockers']);
    }

    // ── ServiceJob helpers ────────────────────────────────────────────────────

    public function test_service_job_vehicle_compatibility_status(): void
    {
        $vehicle = Vehicle::factory()->create([
            'company_id'      => 1,
            'capability_tags' => ['ladder'],
        ]);
        $job = ServiceJob::factory(['company_id' => 1, 'site_id' => null])->create([
            'assigned_vehicle_id'  => $vehicle->id,
            'required_vehicle_type' => 'ladder',
        ]);

        $this->assertTrue($job->vehicleCompatibilityStatus());

        $job2 = ServiceJob::factory(['company_id' => 1, 'site_id' => null])->create([
            'assigned_vehicle_id'  => $vehicle->id,
            'required_vehicle_type' => 'chemical_safe',
        ]);

        $this->assertFalse($job2->vehicleCompatibilityStatus());
    }

    // ── Shift vehicle_id ──────────────────────────────────────────────────────

    public function test_shift_can_be_linked_to_vehicle(): void
    {
        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create(['company_id' => 1]);

        $shift = Shift::factory()->create([
            'company_id' => 1,
            'user_id'    => $user->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $this->assertDatabaseHas('shifts', [
            'id'         => $shift->id,
            'vehicle_id' => $vehicle->id,
        ]);
        $this->assertTrue($shift->vehicle()->exists());
    }

    // ── HTTP API ──────────────────────────────────────────────────────────────

    public function test_assign_job_via_http_returns_json(): void
    {
        Event::fake([VehicleAssignedToJob::class]);

        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create(['company_id' => 1]);
        $job     = ServiceJob::factory(['company_id' => 1, 'site_id' => null])->create();

        $resp = $this->actingAs($user)->postJson(
            route('dashboard.work.vehicles.assign-job', $vehicle),
            ['service_job_id' => $job->id]
        );

        $resp->assertOk()
            ->assertJsonStructure(['assignment_id', 'vehicle_id', 'job_id']);
    }

    public function test_location_snapshot_via_http(): void
    {
        Event::fake([VehicleLocationUpdated::class]);

        $user    = User::factory()->create(['company_id' => 1]);
        $vehicle = Vehicle::factory()->create(['company_id' => 1]);

        $resp = $this->actingAs($user)->postJson(
            route('dashboard.work.vehicles.location-snapshot', $vehicle),
            ['lat' => -33.8688, 'lng' => 151.2093, 'source' => 'mobile']
        );

        $resp->assertOk()
            ->assertJsonStructure(['snapshot_id', 'lat', 'lng', 'captured_at']);
    }
}
