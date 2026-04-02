<?php

declare(strict_types=1);

namespace App\Models\Facility;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Equipment\Equipment;
use App\Models\Premises\Building;
use App\Models\Premises\Premises;
use App\Models\Premises\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Site-installed physical asset.
 *
 * Separate from the Equipment stock catalogue — represents a specific
 * physical installation at a Premises / Unit.
 *
 * Condition status: new | good | fair | poor | decommissioned
 * Status values:    active | removed | replaced | decommissioned
 *
 * Sources: FacilityManagement/Entities/Asset.php,
 *          AssetManagement, ManagedPremises/Entities/PropertyAsset.php.
 */
class SiteAsset extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'site_assets';

    protected $fillable = [
        'company_id',
        'created_by',
        'premises_id',
        'building_id',
        'unit_id',
        'equipment_id',
        'label',
        'asset_code',
        'asset_type',
        'manufacturer',
        'model_number',
        'serial_number',
        'location_description',
        'install_date',
        'commission_date',
        'warranty_expiry',
        'inspection_interval_days',
        'maintenance_interval_days',
        'next_inspection_due',
        'next_maintenance_due',
        'last_serviced_at',
        'condition_status',
        'status',
        'notes',
        'meta',
    ];

    protected $casts = [
        'install_date'             => 'date',
        'commission_date'          => 'date',
        'warranty_expiry'          => 'date',
        'next_inspection_due'      => 'date',
        'next_maintenance_due'     => 'date',
        'last_serviced_at'         => 'date',
        'inspection_interval_days' => 'integer',
        'maintenance_interval_days' => 'integer',
        'meta'                     => 'array',
    ];

    protected $attributes = [
        'condition_status' => 'good',
        'status'           => 'active',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function serviceEvents(): HasMany
    {
        return $this->hasMany(AssetServiceEvent::class, 'site_asset_id')
            ->orderByDesc('event_date');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInspectionDue(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('next_inspection_due', '<=', now()->toDateString());
    }

    public function scopeMaintenanceDue(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('next_maintenance_due', '<=', now()->toDateString());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isUnderWarranty(): bool
    {
        return $this->warranty_expiry !== null && $this->warranty_expiry->isFuture();
    }

    public function isInspectionDue(): bool
    {
        return $this->next_inspection_due !== null
            && $this->next_inspection_due->isPast();
    }

    public function isMaintenanceDue(): bool
    {
        return $this->next_maintenance_due !== null
            && $this->next_maintenance_due->isPast();
    }
}
