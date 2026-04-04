<?php

declare(strict_types=1);

namespace App\Services\Repair;

use App\Events\Repair\RepairOrderCancelled;
use App\Events\Repair\RepairOrderCompleted;
use App\Events\Repair\RepairOrderCreated;
use App\Events\Repair\RepairPartsConsumed;
use App\Events\Repair\RepairPartsReserved;
use App\Events\Repair\RepairScheduled;
use App\Models\Repair\RepairOrder;
use App\Models\Work\ServiceJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * RepairOrderService — canonical orchestrator for the RepairOrder lifecycle.
 *
 * Lifecycle: draft → diagnosed → awaiting_parts → scheduled
 *            → in_progress → completed → closed
 *
 * This service is the single authoritative entry point for:
 *   - creating repair orders (from jobs, warranty claims, or direct intake)
 *   - advancing status transitions
 *   - linking stock / part consumption to the repair record
 *   - closing out and recording the repair outcome
 */
class RepairOrderService
{
    /**
     * Create a new repair order, optionally linked to an originating service job.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): RepairOrder
    {
        return DB::transaction(function () use ($attributes): RepairOrder {
            $order = RepairOrder::create($attributes);

            RepairOrderCreated::dispatch($order);

            return $order;
        });
    }

    /**
     * Create a repair order from a completed / in-progress service job.
     * Propagates customer, premises, agreement, and equipment context automatically.
     */
    public function createFromServiceJob(ServiceJob $job, array $extra = []): RepairOrder
    {
        $attributes = array_merge([
            'company_id'    => $job->company_id,
            'created_by'    => $job->created_by,
            'service_job_id' => $job->id,
            'premises_id'   => $job->premises_id,
            'customer_id'   => $job->customer_id,
            'agreement_id'  => $job->agreement_id,
            'repair_status' => RepairOrder::STATUS_DRAFT,
        ], $extra);

        return $this->create($attributes);
    }

    /**
     * Advance a repair order to the "scheduled" state and emit the RepairScheduled event.
     */
    public function schedule(RepairOrder $order, Carbon $scheduledAt): RepairOrder
    {
        $order->update([
            'repair_status' => RepairOrder::STATUS_SCHEDULED,
            'scheduled_at'  => $scheduledAt,
        ]);

        RepairScheduled::dispatch($order);

        return $order->fresh();
    }

    /**
     * Mark parts as reserved against this repair order.
     *
     * @param  array<string, mixed>  $partAttributes
     */
    public function reserveParts(RepairOrder $order, array $partAttributes): RepairOrder
    {
        $order->partUsages()->create(array_merge($partAttributes, ['status' => 'reserved']));
        $order->update(['repair_status' => RepairOrder::STATUS_AWAITING_PARTS]);

        RepairPartsReserved::dispatch($order);

        return $order->fresh();
    }

    /**
     * Record parts as physically consumed during the repair.
     *
     * @param  array<string, mixed>  $partAttributes
     */
    public function consumeParts(RepairOrder $order, array $partAttributes): RepairOrder
    {
        $order->partUsages()
            ->where('status', 'reserved')
            ->update(['status' => 'consumed', 'consumed_at' => now()]);

        RepairPartsConsumed::dispatch($order);

        return $order->fresh();
    }

    /**
     * Mark a repair order as completed and emit the RepairOrderCompleted event.
     *
     * @param  array<string, mixed>  $resolution
     */
    public function complete(RepairOrder $order, array $resolution = []): RepairOrder
    {
        return DB::transaction(function () use ($order, $resolution): RepairOrder {
            $order->update(array_merge([
                'repair_status' => RepairOrder::STATUS_COMPLETED,
                'completed_at'  => now(),
            ], $resolution));

            RepairOrderCompleted::dispatch($order);

            return $order->fresh();
        });
    }

    /**
     * Close a completed repair order (final archival state).
     */
    public function close(RepairOrder $order): RepairOrder
    {
        $order->update(['repair_status' => RepairOrder::STATUS_CLOSED]);

        return $order->fresh();
    }

    /**
     * Cancel a repair order and emit RepairOrderCancelled.
     */
    public function cancel(RepairOrder $order, string $reason = ''): RepairOrder
    {
        $order->update([
            'repair_status'      => RepairOrder::STATUS_CANCELLED,
            'resolution_summary' => $reason ?: $order->resolution_summary,
        ]);

        RepairOrderCancelled::dispatch($order);

        return $order->fresh();
    }
}
