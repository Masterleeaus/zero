<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Equipment\EquipmentWarranty;
use App\Models\Premises\Building;
use App\Models\Premises\Floor;
use App\Models\Premises\Premises;
use App\Models\Premises\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @deprecated Use App\Models\Facility\SiteAsset (canonical model for the site_assets table).
 *             This class in App\Models\Work\ is a duplicate targeting the same table and will
 *             be removed in a future cleanup pass. All new references should use the Facility
 *             namespace model.
 *
 * SiteAsset — installed infrastructure at a premises.
 *
 * Represents fixed plant and infrastructure (fire panels, sprinkler systems,
 * HVAC ductwork, lifts, electrical boards etc.) that is permanently installed
 * at a site location.
 *
 * Distinct from Equipment (serialised movable devices):
 *   Equipment  = serialised movable devices (tracked by serial number, can move)
 *   SiteAsset  = installed infrastructure (fixed to premises hierarchy)
 *
 * Status: active | decommissioned | under_maintenance | pending_inspection
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
        'name',
        'asset_code',
        'category',
        'status',
        'premises_id',
        'building_id',
        'floor_id',
        'unit_id',
        'customer_id',
        'agreement_id',
        'manufacturer',
        'model',
        'serial_number',
        'install_date',
        'last_service_date',
        'next_service_due',
        'warranty_expiry',
        'warranty_start_date',
        'warranty_provider',
        'warranty_reference',
        'coverage_type',
        'coverage_notes',
        'claimable_until',
        'extended_warranty_flag',
        'warranty_status',
        'location_description',
        'notes',
    ];

    protected $casts = [
        'install_date'           => 'date',
        'last_service_date'      => 'date',
        'next_service_due'       => 'date',
        'warranty_expiry'        => 'date',
        'warranty_start_date'    => 'date',
        'claimable_until'        => 'date',
        'extended_warranty_flag' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'active',
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

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    public function warranties(): HasMany
    {
        return $this->hasMany(EquipmentWarranty::class, 'site_asset_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isUnderWarranty(): bool
    {
        if (! $this->warranty_expiry) {
            return false;
        }

        return $this->warranty_expiry->isFuture();
    }

    /**
     * First active EquipmentWarranty record for this site asset.
     *
     * If multiple active warranties exist (e.g. vendor + extended), the most
     * recently created one is returned. Use `warranties()->active()->get()` to
     * retrieve all active records.
     */
    public function activeWarranty(): ?EquipmentWarranty
    {
        return $this->warranties()->active()->first();
    }

    /** Whether any active warranty record exists. */
    public function hasWarranty(): bool
    {
        return $this->warranties()->active()->exists();
    }

    /** Whether the inline warranty_expiry has elapsed. */
    public function warrantyExpired(): bool
    {
        return $this->warranty_expiry !== null && $this->warranty_expiry->isPast();
    }

    /** Whether warranty expires within the given number of days. */
    public function warrantyExpiresSoon(int $days = 30): bool
    {
        return $this->warranty_expiry !== null
            && $this->warranty_expiry->isFuture()
            && $this->warranty_expiry->diffInDays(now()) <= $days;
    }

    /** Whether a warranty claim can be submitted. */
    public function eligibleForClaim(): bool
    {
        if ($this->claimable_until) {
            return $this->claimable_until->isFuture();
        }

        return $this->isUnderWarranty();
    }

    public function isServiceDue(): bool
    {
        if (! $this->next_service_due) {
            return false;
        }

        return $this->next_service_due->isPast() || $this->next_service_due->isToday();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForPremises(Builder $query, int $premisesId): Builder
    {
        return $query->where('premises_id', $premisesId);
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeServiceDue(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereNotNull('next_service_due')
            ->where('next_service_due', '<=', now()->toDateString());
    }
}
