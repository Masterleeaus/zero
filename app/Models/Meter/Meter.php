<?php

declare(strict_types=1);

namespace App\Models\Meter;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Facility\SiteAsset;
use App\Models\Premises\Premises;
use App\Models\Premises\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Utility meter tracked at a Premises or Unit level.
 *
 * Meter types: water | electricity | gas | hvac_runtime | solar | custom
 * Status:      active | inactive | replaced | removed
 *
 * Supports threshold alerts (threshold_high / threshold_low) and
 * anomaly detection via expected_interval_days.
 *
 * Sources: FacilityManagement/Entities/Meter.php,
 *          ManagedPremises/pm_meter_readings migration.
 */
class Meter extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'meters';

    protected $fillable = [
        'company_id',
        'created_by',
        'premises_id',
        'unit_id',
        'site_asset_id',
        'meter_type',
        'name',
        'barcode',
        'unit_of_measure',
        'threshold_high',
        'threshold_low',
        'expected_interval_days',
        'last_reading',
        'last_read_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'threshold_high'         => 'decimal:3',
        'threshold_low'          => 'decimal:3',
        'last_reading'           => 'decimal:3',
        'last_read_at'           => 'datetime',
        'expected_interval_days' => 'integer',
    ];

    protected $attributes = [
        'meter_type' => 'water',
        'status'     => 'active',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function siteAsset(): BelongsTo
    {
        return $this->belongsTo(SiteAsset::class, 'site_asset_id');
    }

    public function readings(): HasMany
    {
        return $this->hasMany(MeterReading::class, 'meter_id')
            ->orderByDesc('reading_date');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereNotNull('expected_interval_days')
            ->whereNotNull('last_read_at')
            ->whereRaw(
                'DATEDIFF(NOW(), last_read_at) > expected_interval_days'
            );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isReadingAboveThreshold(float $reading): bool
    {
        return $this->threshold_high !== null && $reading > (float) $this->threshold_high;
    }

    public function isReadingBelowThreshold(float $reading): bool
    {
        return $this->threshold_low !== null && $reading < (float) $this->threshold_low;
    }

    public function isReadingAnomalous(float $reading): bool
    {
        return $this->isReadingAboveThreshold($reading)
            || $this->isReadingBelowThreshold($reading);
    }
}
