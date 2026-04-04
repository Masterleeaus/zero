<?php

declare(strict_types=1);

namespace App\Models\Route;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Route\TechnicianAvailability;
use App\Models\Team\Team;
use App\Models\User;
use App\Models\Vehicle\Vehicle;
use App\Models\Work\ServiceArea;
use App\Models\Work\Territory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DispatchRoute — canonical named route template.
 *
 * Maps from Odoo fsm.route:
 *   - name, assigned technician (person), active days, max_stops (max_order)
 *
 * Extended with: team_id, territory_id, service_area_id, blackout groups.
 *
 * A DispatchRoute does not contain execution state; the DispatchRouteStop
 * represents a concrete route-run for a given date.
 */
class DispatchRoute extends Model
{
    use BelongsToCompany;

    protected $table = 'dispatch_routes';

    protected $fillable = [
        'company_id',
        'name',
        'assigned_user_id',
        'team_id',
        'vehicle_id',
        'active_days_mask',
        'max_stops_per_day',
        'territory_id',
        'service_area_id',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'active_days_mask'   => 'integer',
        'max_stops_per_day'  => 'integer',
    ];

    protected $attributes = [
        'status'            => 'active',
        'active_days_mask'  => 0b0011111, // Mon–Fri
        'max_stops_per_day' => 0,
    ];

    // Day name → bitmask bit index (0=Mon, …, 6=Sun)
    public const DAY_BITS = [
        'monday'    => 0,
        'tuesday'   => 1,
        'wednesday' => 2,
        'thursday'  => 3,
        'friday'    => 4,
        'saturday'  => 5,
        'sunday'    => 6,
    ];

    protected $dispatchesEvents = [
        'created' => \App\Events\Route\RouteCreated::class,
        'updated' => \App\Events\Route\RouteUpdated::class,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class, 'territory_id');
    }

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class, 'service_area_id');
    }

    /** All concrete date-level route runs. */
    public function routeStops(): HasMany
    {
        return $this->hasMany(DispatchRouteStop::class, 'route_id');
    }

    /**
     * All technician availability records associated with the route's assigned user.
     *
     * Provides the availability → route linkage at the model layer.
     *
     * @return HasMany<TechnicianAvailability>
     */
    public function technicianAvailabilities(): HasMany
    {
        return $this->hasMany(TechnicianAvailability::class, 'user_id', 'assigned_user_id');
    }

    /** Blackout groups linked to this route. */
    public function blackoutGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            RouteBlackoutGroup::class,
            'dispatch_route_blackout_group',
            'dispatch_route_id',
            'route_blackout_group_id',
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Return true if the route is configured to run on the given date's weekday.
     */
    public function runsOn(\DateTimeInterface $date): bool
    {
        $dayIndex = (int) $date->format('N') - 1; // 0=Mon … 6=Sun
        return (bool) ($this->active_days_mask & (1 << $dayIndex));
    }

    /**
     * Return the day names this route runs on.
     *
     * @return list<string>
     */
    public function activeDayNames(): array
    {
        $names = [];
        foreach (self::DAY_BITS as $name => $bit) {
            if ($this->active_days_mask & (1 << $bit)) {
                $names[] = ucfirst($name);
            }
        }
        return $names;
    }

    /**
     * Capacity remaining for a specific route stop (date-level run).
     * Returns null when no capacity limit is set.
     */
    public function capacityRemainingFor(DispatchRouteStop $stop): ?int
    {
        $max = $stop->max_stops > 0 ? $stop->max_stops : $this->max_stops_per_day;
        if ($max === 0) {
            return null;
        }
        return max(0, $max - $stop->stopItems()->count());
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_user_id', $userId);
    }
}
