<?php

declare(strict_types=1);

namespace App\Models\Vehicle;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VehicleLocationSnapshot — lightweight point-in-time vehicle location record.
 *
 * Maps from Odoo fieldservice_vehicle: location awareness (not full telematics).
 *
 * Consumed by:
 *   - dispatch board
 *   - route optimizer
 *   - owner command app
 *   - timeline overlays
 *
 * Full telematics is explicitly out of scope for this module.
 */
class VehicleLocationSnapshot extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'vehicle_location_snapshots';

    // Source constants
    public const SOURCE_MOBILE  = 'mobile';
    public const SOURCE_GPS     = 'gps';
    public const SOURCE_MANUAL  = 'manual';
    public const SOURCE_SYSTEM  = 'system';

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'lat',
        'lng',
        'captured_at',
        'source',
        'accuracy',
        'notes',
    ];

    protected $casts = [
        'lat'         => 'float',
        'lng'         => 'float',
        'accuracy'    => 'float',
        'captured_at' => 'datetime',
    ];

    protected $attributes = [
        'source' => self::SOURCE_MOBILE,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('captured_at', '>=', now()->subHours($hours));
    }

    public function scopeForVehicle(Builder $query, int $vehicleId): Builder
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Return coordinates as [lat, lng] array. */
    public function coordinates(): array
    {
        return [(float) $this->lat, (float) $this->lng];
    }
}
