<?php

declare(strict_types=1);

namespace App\Models\Vehicle;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Equipment\Equipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VehicleEquipment — equipment item carried by a vehicle.
 *
 * Bridges fieldservice_vehicle with the Equipment domain.
 * Enables dispatch to verify a vehicle carries required equipment
 * before assigning it to a job.
 */
class VehicleEquipment extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'vehicle_equipment';

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'equipment_id',
        'equipment_label',
        'quantity',
        'condition',
        'loaded_at',
        'notes',
    ];

    protected $casts = [
        'quantity'  => 'integer',
        'loaded_at' => 'datetime',
    ];

    protected $attributes = [
        'quantity'  => 1,
        'condition' => 'good',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Optional link to a canonical Equipment record.
     * Some items may be described inline without a linked record.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForVehicle(Builder $query, int $vehicleId): Builder
    {
        return $query->where('vehicle_id', $vehicleId);
    }
}
