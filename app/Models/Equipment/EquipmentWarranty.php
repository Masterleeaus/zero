<?php

declare(strict_types=1);

namespace App\Models\Equipment;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Work\ServiceAgreement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FSM Module 8 — Equipment Warranty
 *
 * Canonical warranty record attached to installed equipment, serialised
 * equipment, or a fixed site asset.
 *
 * Supports multi-coverage warranties, extended warranties, vendor warranties,
 * and agreement-backed warranties.
 *
 * Status values: active | expired | expiring_soon | void | claimed | unknown
 * Coverage types: parts | labour | full | limited | extended
 */
class EquipmentWarranty extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'equipment_warranties';

    protected $fillable = [
        'company_id',
        'created_by',
        'installed_equipment_id',
        'equipment_id',
        'site_asset_id',
        'agreement_id',
        'name',
        'warranty_provider',
        'warranty_reference',
        'coverage_type',
        'coverage_notes',
        'warranty_start_date',
        'warranty_expiry',
        'claimable_until',
        'extended_warranty_flag',
        'warranty_status',
    ];

    protected $casts = [
        'warranty_start_date'   => 'date',
        'warranty_expiry'       => 'date',
        'claimable_until'       => 'date',
        'extended_warranty_flag' => 'boolean',
    ];

    protected $attributes = [
        'warranty_status'        => 'unknown',
        'extended_warranty_flag' => false,
    ];

    // ── Status constants ──────────────────────────────────────────────────────

    public const STATUS_ACTIVE        = 'active';
    public const STATUS_EXPIRED       = 'expired';
    public const STATUS_EXPIRING_SOON = 'expiring_soon';
    public const STATUS_VOID          = 'void';
    public const STATUS_CLAIMED       = 'claimed';
    public const STATUS_UNKNOWN       = 'unknown';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRED,
        self::STATUS_EXPIRING_SOON,
        self::STATUS_VOID,
        self::STATUS_CLAIMED,
        self::STATUS_UNKNOWN,
    ];

    // ── Coverage type constants ───────────────────────────────────────────────

    public const COVERAGE_PARTS    = 'parts';
    public const COVERAGE_LABOUR   = 'labour';
    public const COVERAGE_FULL     = 'full';
    public const COVERAGE_LIMITED  = 'limited';
    public const COVERAGE_EXTENDED = 'extended';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function installedEquipment(): BelongsTo
    {
        return $this->belongsTo(InstalledEquipment::class, 'installed_equipment_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function siteAsset(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Facility\SiteAsset::class, 'site_asset_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class, 'equipment_warranty_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Whether this warranty is currently active (not expired/void). */
    public function isActive(): bool
    {
        return $this->warranty_status === self::STATUS_ACTIVE;
    }

    /** Whether the warranty expiry is in the future. */
    public function isValid(): bool
    {
        if (! $this->warranty_expiry) {
            return false;
        }

        return $this->warranty_expiry->isFuture();
    }

    /** Whether the warranty expires within the given number of days. */
    public function expiresSoon(int $days = 30): bool
    {
        if (! $this->warranty_expiry) {
            return false;
        }

        return $this->warranty_expiry->isFuture()
            && $this->warranty_expiry->diffInDays(now()) <= $days;
    }

    /** Whether a claim can currently be submitted. */
    public function isClaimable(): bool
    {
        if ($this->claimable_until) {
            return $this->claimable_until->isFuture();
        }

        return $this->isValid();
    }

    /** Compute and return the resolved warranty status. */
    public function resolveStatus(): string
    {
        if ($this->warranty_status === self::STATUS_VOID) {
            return self::STATUS_VOID;
        }

        if (! $this->warranty_expiry) {
            return self::STATUS_UNKNOWN;
        }

        if ($this->warranty_expiry->isPast()) {
            return self::STATUS_EXPIRED;
        }

        if ($this->expiresSoon()) {
            return self::STATUS_EXPIRING_SOON;
        }

        return self::STATUS_ACTIVE;
    }

    /** Refresh the warranty_status column based on current dates. */
    public function syncStatus(): void
    {
        $this->update(['warranty_status' => $this->resolveStatus()]);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('warranty_status', self::STATUS_ACTIVE);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereIn('warranty_status', [self::STATUS_ACTIVE, self::STATUS_EXPIRING_SOON])
            ->whereNotNull('warranty_expiry')
            ->whereDate('warranty_expiry', '>', now()->toDateString())
            ->whereDate('warranty_expiry', '<=', now()->addDays($days)->toDateString());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('warranty_status', self::STATUS_EXPIRED);
    }

    public function scopeForEquipment(Builder $query, int $equipmentId): Builder
    {
        return $query->where('equipment_id', $equipmentId);
    }

    public function scopeForInstalledEquipment(Builder $query, int $installedEquipmentId): Builder
    {
        return $query->where('installed_equipment_id', $installedEquipmentId);
    }
}
