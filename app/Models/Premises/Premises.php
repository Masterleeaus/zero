<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Equipment\Equipment;
use App\Models\Equipment\InstalledEquipment;
use App\Models\Facility\SiteAsset;
use App\Models\Meter\Meter;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlan;
use App\Models\Work\InspectionInstance;
use App\Models\Work\ServiceJob;
use App\Models\Work\SiteAsset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Stage C — Premises Foundation
 *
 * Top-level node in the Premises hierarchy.
 * Adapted from CodeToUse/managed-premises/ManagedPremises/Entities/Property.php.
 *
 * Hierarchy: Premises → Building → Floor → Unit → Room
 *
 * Supports: commercial sites, multi-building sites, multi-tenant buildings,
 *           strata structures.
 *
 * Type values: commercial | residential | strata | industrial
 */
class Premises extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'premises';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'premises_code',
        'type',
        'status',
        'address_line1',
        'address_line2',
        'suburb',
        'state',
        'postcode',
        'country',
        'lat',
        'lng',
        'access_notes',
        'hazards',
        'parking_notes',
        'lockbox_code',
        'keys_location',
        'service_window_start',
        'service_window_end',
        'customer_id',
        'service_priority',
        'maintenance_zone',
        'access_level',
    ];

    protected $attributes = [
        'type'   => 'commercial',
        'status' => 'active',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, 'premises_id');
    }

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'premises_id');
    }

    public function installedEquipment(): HasMany
    {
        return $this->hasMany(InstalledEquipment::class, 'premises_id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'premises_id');
    }

    public function siteAssets(): HasMany
    {
        return $this->hasMany(SiteAsset::class, 'premises_id');
    }

    public function meters(): HasMany
    {
        return $this->hasMany(Meter::class, 'premises_id');
    }

    public function servicePlans(): HasMany
    {
        return $this->hasMany(ServicePlan::class, 'premises_id');
    }

    public function hazardRecords(): HasMany
    {
        return $this->hasMany(Hazard::class, 'premises_id');
    }

    public function siteAccessProfile(): HasMany
    {
        return $this->hasMany(SiteAccessProfile::class, 'premises_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(FacilityDocument::class, 'documentable');
    public function inspections(): HasMany
    {
        return $this->hasMany(InspectionInstance::class, 'premises_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Stage G — Active hazards for this premises.
     * Exposes structured hazard records (status = active).
     */
    public function activeHazards(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->hazardRecords()->where('status', 'active')->get();
    }

    /**
     * Active site access profile (most recently created active profile).
     */
    public function activeSiteAccess(): ?SiteAccessProfile
    {
        return $this->siteAccessProfile()
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }
}
