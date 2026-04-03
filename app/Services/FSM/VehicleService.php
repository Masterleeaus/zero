<?php

declare(strict_types=1);

namespace App\Services\FSM;

use App\Events\Work\VehicleAssignedToJob;
use App\Events\Work\VehicleEquipmentMissing;
use App\Events\Work\VehicleLocationUpdated;
use App\Events\Work\VehicleRouteReady;
use App\Events\Work\VehicleStockConsumed;
use App\Events\Work\VehicleStockReserved;
use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleAssignment;
use App\Models\Vehicle\VehicleLocationSnapshot;
use App\Models\Vehicle\VehicleStock;
use App\Models\Work\ServiceJob;
use App\Models\Route\DispatchRoute;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * VehicleService — fieldservice_vehicle + fieldservice_vehicle_stock intelligence layer.
 *
 * Responsibilities:
 *   - assign vehicles to jobs, routes, and shifts
 *   - check vehicle compatibility against job requirements
 *   - manage vehicle stock reservations and consumption
 *   - record location snapshots
 *   - emit lifecycle events for dispatch board and route optimizer
 *
 * Public API:
 *   assignVehicleToJob(Vehicle, ServiceJob)              → VehicleAssignment
 *   releaseVehicleFromJob(ServiceJob)                    → void
 *   checkJobCompatibility(Vehicle, ServiceJob)           → array
 *   reserveStockForJob(Vehicle, ServiceJob, array)       → void
 *   consumeStockOnJob(Vehicle, ServiceJob, array)        → void
 *   recordLocationSnapshot(Vehicle, array)               → VehicleLocationSnapshot
 *   getRouteReadinessStatus(Vehicle, ServiceJob)         → array
 *   findCompatibleVehicles(ServiceJob)                   → Collection<Vehicle>
 */
class VehicleService
{
    // ── Assignment ────────────────────────────────────────────────────────────

    /**
     * Assign a vehicle to a service job.
     *
     * Updates the job's assigned_vehicle_id and creates a VehicleAssignment
     * record for history. Emits VehicleAssignedToJob.
     */
    public function assignVehicleToJob(Vehicle $vehicle, ServiceJob $job): VehicleAssignment
    {
        return DB::transaction(function () use ($vehicle, $job) {
            // End any existing active assignment for this vehicle
            VehicleAssignment::where('vehicle_id', $vehicle->id)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            $assignment = VehicleAssignment::create([
                'company_id'       => $job->company_id,
                'vehicle_id'       => $vehicle->id,
                'assignable_type'  => VehicleAssignment::ENTITY_SERVICE_JOB,
                'assignable_id'    => $job->id,
                'assigned_by'      => auth()->id(),
                'started_at'       => now(),
            ]);

            $job->assigned_vehicle_id = $vehicle->id;
            $job->save();

            $vehicle->status = Vehicle::STATUS_IN_USE;
            $vehicle->save();

            event(new VehicleAssignedToJob($vehicle, $job));

            return $assignment;
        });
    }

    /**
     * Release the vehicle currently assigned to a job.
     */
    public function releaseVehicleFromJob(ServiceJob $job): void
    {
        DB::transaction(function () use ($job) {
            VehicleAssignment::where('assignable_type', VehicleAssignment::ENTITY_SERVICE_JOB)
                ->where('assignable_id', $job->id)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            if ($job->assigned_vehicle_id) {
                $vehicle = Vehicle::find($job->assigned_vehicle_id);
                if ($vehicle && $vehicle->status === Vehicle::STATUS_IN_USE) {
                    $vehicle->status = Vehicle::STATUS_ACTIVE;
                    $vehicle->save();
                }
            }

            $job->assigned_vehicle_id = null;
            $job->save();
        });
    }

    /**
     * Assign a vehicle to a dispatch route.
     */
    public function assignVehicleToRoute(Vehicle $vehicle, DispatchRoute $route): VehicleAssignment
    {
        return DB::transaction(function () use ($vehicle, $route) {
            $assignment = VehicleAssignment::create([
                'company_id'       => $route->company_id,
                'vehicle_id'       => $vehicle->id,
                'assignable_type'  => VehicleAssignment::ENTITY_DISPATCH_ROUTE,
                'assignable_id'    => $route->id,
                'assigned_by'      => auth()->id(),
                'started_at'       => now(),
            ]);

            $route->vehicle_id = $vehicle->id;
            $route->save();

            return $assignment;
        });
    }

    // ── Compatibility ─────────────────────────────────────────────────────────

    /**
     * Check whether a vehicle is compatible with a service job's requirements.
     *
     * Returns a status array with computed readiness flags consumed by the
     * dispatch engine and kanban board.
     *
     * @return array{
     *   vehicle_route_ready: bool,
     *   vehicle_stock_ready: bool,
     *   vehicle_equipment_ready: bool,
     *   vehicle_capacity_ok: bool,
     *   vehicle_location_ok: bool,
     *   blockers: array<string>
     * }
     */
    public function checkJobCompatibility(Vehicle $vehicle, ServiceJob $job): array
    {
        $blockers = [];

        // ── Vehicle type / capability constraint ─────────────────────────────
        $equipmentReady = true;
        if ($job->required_vehicle_type) {
            $required = array_filter(
                array_map('trim', explode(',', $job->required_vehicle_type))
            );
            if (! $vehicle->hasCapabilities($required)) {
                $equipmentReady = false;
                $blockers[]     = 'vehicle_capability_mismatch';
                event(new VehicleEquipmentMissing($vehicle, $job, $required));
            }
        }

        // ── Equipment availability ────────────────────────────────────────────
        // (simple check: vehicle has at least one equipment item loaded)
        $vehicleHasEquipment = $vehicle->vehicleEquipment()->exists();

        // ── Stock readiness ───────────────────────────────────────────────────
        $stockReady = true;
        $jobStock   = VehicleStock::where('vehicle_id', $vehicle->id)
            ->where('reserved_for_job_id', $job->id)
            ->where('status', VehicleStock::STATUS_RESERVED)
            ->exists();

        // If the job has reserved stock, it's stock-ready; if no reservation
        // was made at all treat as acceptable (job may not need vehicle stock).
        $stockReady = $jobStock || ! VehicleStock::where('vehicle_id', $vehicle->id)
            ->where('status', VehicleStock::STATUS_RESERVED)
            ->exists();

        // ── Location readiness ────────────────────────────────────────────────
        $snapshot = $vehicle->latestLocation();
        // Location is "ok" if we have a snapshot captured within the last 12 h
        $locationOk = $snapshot !== null
            && $snapshot->captured_at->greaterThan(now()->subHours(12));

        // ── Capacity (lightweight check: vehicle is active) ───────────────────
        $capacityOk = $vehicle->status !== Vehicle::STATUS_RETIRED
            && $vehicle->status !== Vehicle::STATUS_SERVICING;

        if (! $capacityOk) {
            $blockers[] = 'vehicle_unavailable';
        }

        $routeReady = empty($blockers) && $capacityOk && $equipmentReady;

        if ($routeReady) {
            event(new VehicleRouteReady($vehicle, $job));
        }

        return [
            'vehicle_route_ready'     => $routeReady,
            'vehicle_stock_ready'     => $stockReady,
            'vehicle_equipment_ready' => $equipmentReady,
            'vehicle_capacity_ok'     => $capacityOk,
            'vehicle_location_ok'     => $locationOk,
            'blockers'                => $blockers,
        ];
    }

    /**
     * Find vehicles that are compatible with a job's requirements.
     *
     * @return Collection<int, Vehicle>
     */
    public function findCompatibleVehicles(ServiceJob $job): Collection
    {
        $query = Vehicle::where('company_id', $job->company_id)
            ->whereIn('status', [Vehicle::STATUS_ACTIVE]);

        if ($job->required_vehicle_type) {
            // Filter by JSON capability_tags containing the required type
            $required = array_filter(
                array_map('trim', explode(',', $job->required_vehicle_type))
            );
            foreach ($required as $cap) {
                $query->whereJsonContains('capability_tags', $cap);
            }
        }

        if ($job->team_id) {
            $query->where(static function ($q) use ($job) {
                $q->where('team_id', $job->team_id)->orWhereNull('team_id');
            });
        }

        return $query->get();
    }

    // ── Stock ─────────────────────────────────────────────────────────────────

    /**
     * Reserve stock items on a vehicle for a specific job.
     *
     * @param  array<array{sku: string, quantity: float}>  $items
     */
    public function reserveStockForJob(Vehicle $vehicle, ServiceJob $job, array $items): void
    {
        DB::transaction(function () use ($vehicle, $job, $items) {
            foreach ($items as $item) {
                $stock = VehicleStock::where('vehicle_id', $vehicle->id)
                    ->where('sku', $item['sku'])
                    ->where('status', VehicleStock::STATUS_AVAILABLE)
                    ->first();

                if ($stock) {
                    $stock->reserveForJob($job, (float) $item['quantity']);
                    event(new VehicleStockReserved($vehicle, $job, $stock));
                }
            }
        });
    }

    /**
     * Record consumption of stock items on a job site.
     *
     * @param  array<array{sku: string, quantity: float}>  $items
     */
    public function consumeStockOnJob(Vehicle $vehicle, ServiceJob $job, array $items): void
    {
        DB::transaction(function () use ($vehicle, $job, $items) {
            foreach ($items as $item) {
                $stock = VehicleStock::where('vehicle_id', $vehicle->id)
                    ->where('sku', $item['sku'])
                    ->first();

                if ($stock) {
                    $stock->consume((float) $item['quantity']);
                    event(new VehicleStockConsumed($vehicle, $job, $stock));
                }
            }
        });
    }

    // ── Location ──────────────────────────────────────────────────────────────

    /**
     * Record a new location snapshot for a vehicle.
     *
     * @param  array{lat: float, lng: float, source?: string, accuracy?: float}  $coords
     */
    public function recordLocationSnapshot(Vehicle $vehicle, array $coords): VehicleLocationSnapshot
    {
        $snapshot = VehicleLocationSnapshot::create([
            'company_id'  => $vehicle->company_id,
            'vehicle_id'  => $vehicle->id,
            'lat'         => $coords['lat'],
            'lng'         => $coords['lng'],
            'captured_at' => Carbon::now(),
            'source'      => $coords['source'] ?? VehicleLocationSnapshot::SOURCE_MOBILE,
            'accuracy'    => $coords['accuracy'] ?? null,
        ]);

        event(new VehicleLocationUpdated($vehicle, $snapshot));

        return $snapshot;
    }

    // ── Route readiness summary ───────────────────────────────────────────────

    /**
     * Full route-readiness summary for a vehicle/job pair.
     * Alias of checkJobCompatibility with richer labels.
     *
     * @return array<string, mixed>
     */
    public function getRouteReadinessStatus(Vehicle $vehicle, ServiceJob $job): array
    {
        return $this->checkJobCompatibility($vehicle, $job);
    }
}
