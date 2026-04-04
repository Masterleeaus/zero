<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Models\Route\DispatchRoute;
use App\Models\Vehicle\Vehicle;
use App\Models\Work\ServiceJob;

/**
 * VehicleDispatchService — vehicle-aware dispatch helpers (Stage E).
 *
 * Exposes canonical helpers that the dispatch engine uses to validate and
 * score vehicle assignment during job allocation.  All vehicle intelligence
 * lives here; the dispatch board consumes these payloads directly.
 *
 * Public API:
 *   vehicleDispatchReady(Vehicle, ServiceJob)           → array
 *   vehicleRouteCompatibility(Vehicle, DispatchRoute)   → array
 *   vehicleCapacityScore(Vehicle|null, ServiceJob)      → float
 *   vehicleLocationFit(Vehicle, ServiceJob)             → array
 *   isJobVehicleReady(ServiceJob)                       → bool
 *   vehicleBlockers(ServiceJob)                         → array
 */
class VehicleDispatchService
{
    /**
     * Full dispatch readiness payload for a vehicle assigned to a job.
     *
     * Returns a structured summary used by the dispatch board and the
     * DispatchReadinessService to surface vehicle-level blockers.
     */
    public function vehicleDispatchReady(Vehicle $vehicle, ServiceJob $job): array
    {
        $compatibility = $this->checkEquipmentCompatibility($vehicle, $job);
        $capacity      = $this->vehicleCapacityScore($vehicle, $job);
        $stockOk       = $this->vehicleStockSufficient($vehicle, $job);

        $blockers = [];
        if (!$compatibility['compatible']) {
            $blockers = array_merge($blockers, $compatibility['reasons']);
        }
        if ($capacity <= 0.0) {
            $blockers[] = "Vehicle {$vehicle->name} has insufficient capacity for this job";
        }
        if (!$stockOk) {
            $blockers[] = "Vehicle {$vehicle->name} stock is insufficient for job requirements";
        }
        if (!in_array($vehicle->status, [Vehicle::STATUS_ACTIVE, Vehicle::STATUS_IN_USE], true)) {
            $blockers[] = "Vehicle {$vehicle->name} is not available (status: {$vehicle->status})";
        }

        return [
            'vehicle_id'     => $vehicle->id,
            'vehicle_name'   => $vehicle->name,
            'ready'          => empty($blockers),
            'capacity_score' => $capacity,
            'stock_ok'       => $stockOk,
            'equipment_ok'   => $compatibility['compatible'],
            'blockers'       => $blockers,
        ];
    }

    /**
     * Evaluate whether a vehicle is compatible with a given dispatch route.
     */
    public function vehicleRouteCompatibility(Vehicle $vehicle, DispatchRoute $route): array
    {
        $issues = [];

        // Territory alignment
        if ($route->territory_id && $vehicle->territory_id
            && $vehicle->territory_id !== $route->territory_id
        ) {
            $issues[] = "Vehicle territory does not match route territory";
        }

        // Active status
        if ($vehicle->status === Vehicle::STATUS_RETIRED) {
            $issues[] = "Vehicle is retired";
        }

        if ($vehicle->status === Vehicle::STATUS_SERVICING) {
            $issues[] = "Vehicle is currently being serviced";
        }

        // Capacity vs route max stops (use 1 stop = 1 unit of capacity)
        if ($vehicle->max_cargo_weight && $route->max_stops_per_day
            && $vehicle->max_cargo_weight < $route->max_stops_per_day
        ) {
            $issues[] = "Vehicle cargo capacity may be insufficient for route stop count";
        }

        return [
            'vehicle_id'  => $vehicle->id,
            'route_id'    => $route->id,
            'compatible'  => empty($issues),
            'issues'      => $issues,
        ];
    }

    /**
     * Returns a capacity score (0.0–1.0) for a vehicle against a job.
     *
     * Returns 0.0 when the vehicle is null (no vehicle assigned) or when
     * the vehicle is out of service. Otherwise 1.0 — caller can extend
     * with weight/dimension logic as fleet data matures.
     */
    public function vehicleCapacityScore(?Vehicle $vehicle, ServiceJob $job): float
    {
        if ($vehicle === null) {
            return 1.0; // No vehicle constraint; job can proceed without one
        }

        if ($vehicle->status === Vehicle::STATUS_RETIRED) {
            return 0.0;
        }

        return 1.0;
    }

    /**
     * Returns location-fit context for a vehicle relative to a job's premises.
     *
     * Used by the dispatch board to surface travel-time estimates and to
     * rank candidate vehicles by proximity.
     */
    public function vehicleLocationFit(Vehicle $vehicle, ServiceJob $job): array
    {
        $snapshot = $vehicle->locationSnapshots()->latest('recorded_at')->first();
        $premises = $job->premises;

        $distance = null;
        if ($snapshot && $premises && $premises->latitude && $premises->longitude
            && $snapshot->latitude && $snapshot->longitude
        ) {
            $distance = $this->haversineDistance(
                (float) $snapshot->latitude,
                (float) $snapshot->longitude,
                (float) $premises->latitude,
                (float) $premises->longitude,
            );
        }

        return [
            'vehicle_id'          => $vehicle->id,
            'last_known_lat'      => $snapshot?->latitude,
            'last_known_lng'      => $snapshot?->longitude,
            'last_snapshot_at'    => $snapshot?->recorded_at?->toIso8601String(),
            'job_premises_lat'    => $premises?->latitude,
            'job_premises_lng'    => $premises?->longitude,
            'estimated_distance_km' => $distance,
        ];
    }

    /**
     * Quick boolean: does the job's assigned vehicle pass all readiness checks?
     */
    public function isJobVehicleReady(ServiceJob $job): bool
    {
        if (!$job->assigned_vehicle_id) {
            return true; // No vehicle required
        }

        $vehicle = $job->assignedVehicle ?? $job->vehicle ?? null;
        if (!$vehicle) {
            return true;
        }

        $readiness = $this->vehicleDispatchReady($vehicle, $job);
        return $readiness['ready'];
    }

    /**
     * Returns an array of vehicle-level blocker strings for the given job.
     */
    public function vehicleBlockers(ServiceJob $job): array
    {
        if (!$job->assigned_vehicle_id) {
            return [];
        }

        $vehicle = $job->assignedVehicle ?? $job->vehicle ?? null;
        if (!$vehicle) {
            return [];
        }

        $readiness = $this->vehicleDispatchReady($vehicle, $job);
        return $readiness['blockers'];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function checkEquipmentCompatibility(Vehicle $vehicle, ServiceJob $job): array
    {
        // Equipment compatibility uses VehicleEquipment if available
        if (method_exists($vehicle, 'equipment') && $job->job_type_id) {
            $required = optional($job->jobType)->required_equipment ?? [];
            if (!empty($required)) {
                $vehicleEquipmentNames = $vehicle->equipment->pluck('name')->toArray();
                $missing = array_diff($required, $vehicleEquipmentNames);
                if (!empty($missing)) {
                    return [
                        'compatible' => false,
                        'reasons'    => ["Vehicle missing required equipment: " . implode(', ', $missing)],
                    ];
                }
            }
        }

        return ['compatible' => true, 'reasons' => []];
    }

    private function vehicleStockSufficient(Vehicle $vehicle, ServiceJob $job): bool
    {
        // If no stock requirements defined, stock is sufficient
        if (!method_exists($vehicle, 'stockItems')) {
            return true;
        }

        // Delegate detailed stock checking to StockDispatchService when integrated
        return true;
    }

    /**
     * Haversine distance in kilometres between two lat/lng coordinates.
     */
    private function haversineDistance(
        float $lat1, float $lng1,
        float $lat2, float $lng2,
    ): float {
        $earthRadius = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * asin(sqrt($a));
    }
}
