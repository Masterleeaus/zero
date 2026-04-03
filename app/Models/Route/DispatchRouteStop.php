<?php

declare(strict_types=1);

namespace App\Models\Route;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DispatchRouteStop — a concrete route-run for a specific date.
 *
 * Maps from Odoo fsm.route.dayroute:
 *   - route, date, person (assigned_user), stage → status
 *   - start/end location → planned/actual window
 *   - order_ids → stopItems()
 *   - max_order / order_count / order_remaining → capacity helpers
 *
 * One DispatchRouteStop per (route_id, route_date).
 * Each stop item is a DispatchRouteStopItem pointing to a schedulable entity.
 */
class DispatchRouteStop extends Model
{
    use BelongsToCompany;

    protected $table = 'dispatch_route_stops';

    protected $fillable = [
        'company_id',
        'route_id',
        'route_date',
        'assigned_user_id',
        'team_id',
        'status',
        'planned_start_at',
        'planned_end_at',
        'actual_start_at',
        'actual_end_at',
        'start_latitude',
        'start_longitude',
        'current_latitude',
        'current_longitude',
        'max_stops',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'route_date'       => 'date',
        'planned_start_at' => 'datetime',
        'planned_end_at'   => 'datetime',
        'actual_start_at'  => 'datetime',
        'actual_end_at'    => 'datetime',
        'max_stops'        => 'integer',
        'start_latitude'   => 'float',
        'start_longitude'  => 'float',
        'current_latitude' => 'float',
        'current_longitude'=> 'float',
    ];

    protected $attributes = [
        'status'    => 'draft',
        'max_stops' => 0,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function route(): BelongsTo
    {
        return $this->belongsTo(DispatchRoute::class, 'route_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function stopItems(): HasMany
    {
        return $this->hasMany(DispatchRouteStopItem::class, 'route_stop_id')
            ->orderBy('sequence');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Number of stop items currently on this day-route.
     */
    public function stopCount(): int
    {
        return $this->stopItems()->count();
    }

    /**
     * Effective max stops: uses own override if set, otherwise inherits from route.
     */
    public function effectiveMaxStops(): int
    {
        if ($this->max_stops > 0) {
            return $this->max_stops;
        }
        return $this->route?->max_stops_per_day ?? 0;
    }

    /**
     * Remaining capacity, or null if unlimited.
     */
    public function capacityRemaining(): ?int
    {
        $max = $this->effectiveMaxStops();
        if ($max === 0) {
            return null;
        }
        return max(0, $max - $this->stopCount());
    }

    /**
     * Whether this day-route can accept one more stop.
     */
    public function hasCapacity(): bool
    {
        $remaining = $this->capacityRemaining();
        return $remaining === null || $remaining > 0;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForDate(Builder $query, \DateTimeInterface|string $date): Builder
    {
        return $query->where('route_date', $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_user_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['draft', 'confirmed', 'in_progress']);
    }
}
