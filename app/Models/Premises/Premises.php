<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Equipment\Equipment;
use App\Models\Equipment\InstalledEquipment;
use App\Models\Work\InspectionInstance;
use App\Models\Work\ServiceJob;
use App\Models\Work\SiteAsset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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

    public function inspections(): HasMany
    {
        return $this->hasMany(InspectionInstance::class, 'premises_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
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
