<?php

declare(strict_types=1);

namespace App\Models\Vehicle;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Route\DispatchRoute;
use App\Models\Work\Shift;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VehicleAssignment — links a Vehicle to a ServiceJob, DispatchRoute, or Shift.
 *
 * Maps from Odoo fieldservice_vehicle: vehicle field on fsm.order / fsm.person.
 *
 * One vehicle can be assigned to a job, route run, or shift at a time.
 * Ended assignments are retained for history.
 */
class VehicleAssignment extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'vehicle_assignments';

    // Assignable entity types
    public const ENTITY_SERVICE_JOB    = 'service_job';
    public const ENTITY_DISPATCH_ROUTE = 'dispatch_route';
    public const ENTITY_SHIFT          = 'shift';

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'assignable_type',
        'assignable_id',
        'assigned_by',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** Polymorphic assignable (ServiceJob, DispatchRoute, or Shift). */
    public function assignable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('ended_at');
    }

    public function scopeForJob(Builder $query, int $jobId): Builder
    {
        return $query->where('assignable_type', self::ENTITY_SERVICE_JOB)
            ->where('assignable_id', $jobId);
    }

    public function scopeForRoute(Builder $query, int $routeId): Builder
    {
        return $query->where('assignable_type', self::ENTITY_DISPATCH_ROUTE)
            ->where('assignable_id', $routeId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }
}
