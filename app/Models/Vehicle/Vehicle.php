<?php

declare(strict_types=1);

namespace App\Models\Vehicle;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Vehicle — canonical crew vehicle for field service operations.
 *
 * Maps from Odoo fieldservice_vehicle: fsm.vehicle
 *   - name, assigned driver (fsm.person), registration, capacity
 *
 * Extended with: team_id, vehicle type, capability tags, notes.
 */
class Vehicle extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'vehicles';

    // Vehicle type constants
    public const TYPE_VAN          = 'van';
    public const TYPE_TRUCK        = 'truck';
    public const TYPE_CAR          = 'car';
    public const TYPE_MOTORCYCLE   = 'motorcycle';
    public const TYPE_TRAILER      = 'trailer';
    public const TYPE_OTHER        = 'other';

    public const TYPES = [
        self::TYPE_VAN,
        self::TYPE_TRUCK,
        self::TYPE_CAR,
        self::TYPE_MOTORCYCLE,
        self::TYPE_TRAILER,
        self::TYPE_OTHER,
    ];

    // Status constants
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_IN_USE    = 'in_use';
    public const STATUS_SERVICING = 'servicing';
    public const STATUS_RETIRED   = 'retired';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'registration',
        'vehicle_type',
        'team_id',
        'assigned_driver_id',
        'make',
        'model',
        'year',
        'capacity_kg',
        'capability_tags',
        'status',
        'notes',
    ];

    protected $casts = [
        'capability_tags' => 'array',
        'capacity_kg'     => 'integer',
        'year'            => 'integer',
    ];

    protected $attributes = [
        'vehicle_type' => self::TYPE_VAN,
        'status'       => self::STATUS_ACTIVE,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    /** Current and historical job assignments for this vehicle. */
    public function assignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class, 'vehicle_id');
    }

    /** Stock items currently onboard this vehicle. */
    public function stockItems(): HasMany
    {
        return $this->hasMany(VehicleStock::class, 'vehicle_id');
    }

    /** Equipment carried by this vehicle. */
    public function vehicleEquipment(): HasMany
    {
        return $this->hasMany(VehicleEquipment::class, 'vehicle_id');
    }

    /** Location history snapshots. */
    public function locationSnapshots(): HasMany
    {
        return $this->hasMany(VehicleLocationSnapshot::class, 'vehicle_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE]);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('vehicle_type', $type);
    }

    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    // ── Computed helpers ──────────────────────────────────────────────────────

    /**
     * Whether this vehicle has all of the given capability tags.
     *
     * @param  array<string>  $required
     */
    public function hasCapabilities(array $required): bool
    {
        $tags = $this->capability_tags ?? [];

        foreach ($required as $cap) {
            if (! in_array($cap, $tags, true)) {
                return false;
            }
        }

        return true;
    }

    /** Latest location snapshot, if any. */
    public function latestLocation(): ?VehicleLocationSnapshot
    {
        return $this->locationSnapshots()->latest('captured_at')->first();
    }

    /** Currently active assignment (vehicle currently on a job or route). */
    public function activeAssignment(): ?VehicleAssignment
    {
        return $this->assignments()
            ->whereNull('ended_at')
            ->latest()
            ->first();
    }
}
