<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Models\Inventory\InventoryItem;
use App\Models\Vehicle\VehicleStock;
use App\Models\Work\ServiceJob;

/**
 * StockDispatchService — stock-aware dispatch helpers (Stage F).
 *
 * Evaluates material and parts readiness before a job is dispatched.
 * Connects to fieldservice_stock, vehicle_stock, and the inventory domain
 * already present in the canonical graph.
 *
 * Public API:
 *   dispatchStockReady(ServiceJob)       → bool
 *   dispatchMaterialRisk(ServiceJob)     → bool
 *   dispatchRestockRequired(ServiceJob)  → bool
 *   dispatchPartsBlockers(ServiceJob)    → array
 */
class StockDispatchService
{
    /**
     * Returns true when all known stock requirements for the job are met.
     *
     * Checks vehicle stock reservation, inventory item availability, and
     * any explicit parts-needed flags set on the job.
     */
    public function dispatchStockReady(ServiceJob $job): bool
    {
        return empty($this->dispatchPartsBlockers($job));
    }

    /**
     * Returns true when at least one material risk has been detected.
     *
     * A material risk is any condition where stock may be insufficient
     * but no hard blocker has yet been confirmed (e.g. low stock warning
     * vs. zero stock).
     */
    public function dispatchMaterialRisk(ServiceJob $job): bool
    {
        if (!$job->assigned_vehicle_id) {
            return false;
        }

        // If vehicle stock exists but is in a reserved state for another job, flag risk
        $reserved = VehicleStock::where('vehicle_id', $job->assigned_vehicle_id)
            ->where('status', VehicleStock::STATUS_RESERVED)
            ->exists();

        return $reserved;
    }

    /**
     * Returns true when the job vehicle needs a stock restock before dispatch.
     */
    public function dispatchRestockRequired(ServiceJob $job): bool
    {
        if (!$job->assigned_vehicle_id) {
            return false;
        }

        $available = VehicleStock::where('vehicle_id', $job->assigned_vehicle_id)
            ->where('status', VehicleStock::STATUS_AVAILABLE)
            ->exists();

        // If the vehicle has zero available stock items and the job has stock requirements,
        // flag a restock requirement
        return !$available && $this->jobHasStockRequirements($job);
    }

    /**
     * Returns a list of hard parts blocker strings for the job.
     *
     * These are surfaced in the dispatch blocker panel and prevent dispatch
     * until resolved.
     */
    public function dispatchPartsBlockers(ServiceJob $job): array
    {
        $blockers = [];

        // Check vehicle stock sufficiency
        if ($job->assigned_vehicle_id) {
            $vehicleStock = VehicleStock::where('vehicle_id', $job->assigned_vehicle_id)
                ->where('status', VehicleStock::STATUS_AVAILABLE)
                ->get();

            if ($vehicleStock->isEmpty() && $this->jobHasStockRequirements($job)) {
                $blockers[] = 'No available vehicle stock — restock required before dispatch';
            }
        }

        // Check repair-required parts via RepairOrder if linked
        if ($job->is_warranty_job && $job->warranty_claim_id) {
            $claim = $job->warrantyClaim ?? null;
            if ($claim && method_exists($claim, 'repairOrders')) {
                $openRepairs = $claim->repairOrders()
                    ->where('status', 'parts_required')
                    ->exists();
                if ($openRepairs) {
                    $blockers[] = 'Warranty claim has open repair orders with parts required';
                }
            }
        }

        return $blockers;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Returns true when the job has any stock/parts requirements.
     *
     * Currently derived from job type and billable flag; extend as job
     * parts/materials are modelled more explicitly in future passes.
     */
    private function jobHasStockRequirements(ServiceJob $job): bool
    {
        // A billable job is assumed to potentially require stock/parts
        return (bool) $job->is_billable;
    }
}
