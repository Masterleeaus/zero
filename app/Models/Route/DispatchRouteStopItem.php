<?php

declare(strict_types=1);

namespace App\Models\Route;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Premises\Premises;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * DispatchRouteStopItem — an ordered stop within a DispatchRouteStop.
 *
 * Polymorphic link to the canonical schedulable entity being stopped at:
 *   - ServiceJob
 *   - ServicePlanVisit
 *   - InspectionInstance
 *   - ChecklistRun
 *
 * A route stop item is NEVER a standalone work order — it always references
 * an existing schedulable entity.
 *
 * Premises context is snapshotted at dispatch time to avoid joins during
 * real-time dispatch board rendering.
 */
class DispatchRouteStopItem extends Model
{
    use BelongsToCompany;

    protected $table = 'dispatch_route_stop_items';

    protected $fillable = [
        'company_id',
        'route_stop_id',
        'schedulable_type',
        'schedulable_id',
        'sequence',
        'premises_id',
        'customer_id',
        'estimated_arrival_at',
        'actual_arrival_at',
        'actual_departure_at',
        'estimated_duration_minutes',
        'status',
        'dispatch_notes',
    ];

    protected $casts = [
        'sequence'                  => 'integer',
        'estimated_arrival_at'      => 'datetime',
        'actual_arrival_at'         => 'datetime',
        'actual_departure_at'       => 'datetime',
        'estimated_duration_minutes'=> 'integer',
    ];

    protected $attributes = [
        'status'   => 'pending',
        'sequence' => 0,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(DispatchRouteStop::class, 'route_stop_id');
    }

    /**
     * Polymorphic reference to the schedulable entity.
     * Resolves to ServiceJob, ServicePlanVisit, InspectionInstance, or ChecklistRun.
     */
    public function schedulable(): MorphTo
    {
        return $this->morphTo('schedulable');
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Return a compact array for dispatch board rendering without additional queries.
     *
     * @return array<string, mixed>
     */
    public function toDispatchContext(): array
    {
        return [
            'stop_item_id'               => $this->id,
            'route_stop_id'              => $this->route_stop_id,
            'sequence'                   => $this->sequence,
            'schedulable_type'           => $this->schedulable_type,
            'schedulable_id'             => $this->schedulable_id,
            'premises_id'                => $this->premises_id,
            'customer_id'                => $this->customer_id,
            'estimated_arrival_at'       => $this->estimated_arrival_at?->toIso8601String(),
            'actual_arrival_at'          => $this->actual_arrival_at?->toIso8601String(),
            'actual_departure_at'        => $this->actual_departure_at?->toIso8601String(),
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'status'                     => $this->status,
            'dispatch_notes'             => $this->dispatch_notes,
        ];
    }
}
