<?php

declare(strict_types=1);

namespace App\Models\Equipment;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tracks a piece of equipment as site-installed.
 *
 * Separate from stock inventory — this record represents a physical installation
 * at a site/premises, with dates and status.
 *
 * Status values: active | removed | replaced
 */
class InstalledEquipment extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'installed_equipment';

    protected $fillable = [
        'company_id',
        'created_by',
        'equipment_id',
        'site_id',
        'premises_id',
        'customer_id',
        'service_job_id',
        'agreement_id',
        'sale_quote_id',
        'coverage_start_date',
        'coverage_end_date',
        'coverage_activated_at',
        'installed_at',
        'removed_at',
        'status',
        'location_description',
        'notes',
        'warranty_start_date',
        'warranty_expiry',
        'warranty_provider',
        'warranty_reference',
        'coverage_type',
        'coverage_notes',
        'claimable_until',
        'extended_warranty_flag',
        'warranty_status',
    ];

    protected $casts = [
        'installed_at'           => 'date',
        'removed_at'             => 'date',
        'warranty_start_date'    => 'date',
        'warranty_expiry'        => 'date',
        'claimable_until'        => 'date',
        'extended_warranty_flag' => 'boolean',
        'coverage_start_date'    => 'date',
        'coverage_end_date'      => 'date',
        'coverage_activated_at'  => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Premises\Premises::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Crm\Customer::class);
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function warranties(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EquipmentWarranty::class, 'installed_equipment_id');
    }

    // ── fieldservice_sale_agreement_equipment_stock helpers ──────────────────

    /**
     * The ServiceAgreement that covers this installed equipment unit.
     *
     * Mirrors Odoo fieldservice_sale_agreement_equipment_stock:
     *   installed_equipment → service_agreement.
     */
    public function agreementCoverage(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Work\ServiceAgreement::class, 'agreement_id');
    }

    /**
     * The Quote through which this equipment was sold / coverage was activated.
     */
    public function coverageOriginSale(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Money\Quote::class, 'sale_quote_id');
    }

    /**
     * ServicePlanVisits scoped to this installed equipment unit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coverageVisits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Work\ServicePlanVisit::class, 'installed_equipment_id');
    }

    /**
     * Upcoming scheduled or pending visits for this equipment unit.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Work\ServicePlanVisit>
     */
    public function maintenanceSchedule(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->coverageVisits()
            ->whereIn('status', ['pending', 'scheduled'])
            ->whereDate('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->get();
    }

    /**
     * Whether this equipment currently has active agreement coverage.
     */
    public function hasCoverageAgreement(): bool
    {
        return $this->agreement_id !== null
            && $this->coverage_activated_at !== null
            && ($this->coverage_end_date === null || $this->coverage_end_date->isFuture());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Whether this installation is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * First active EquipmentWarranty record for this installation.
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

    /** Whether the warranty expires within the given number of days. */
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

        return $this->warranty_expiry !== null && $this->warranty_expiry->isFuture();
    }

    /**
     * Service jobs performed at the same premises as this installation.
     *
     * Provides a maintenance history proxy until a dedicated service log is added.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ServiceJob>
     */
    public function maintenanceHistory(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->premises_id) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return ServiceJob::query()
            ->where('premises_id', $this->premises_id)
            ->where('company_id', $this->company_id)
            ->where('status', 'completed')
            ->orderByDesc('date_end')
            ->get();
    }

    /**
     * Inspection instances scoped to this installed equipment's premises.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inspection\InspectionInstance>
     */
    public function inspectionHistory(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->premises_id) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return \App\Models\Inspection\InspectionInstance::query()
            ->where('scope_type', \App\Models\Premises\Premises::class)
            ->where('scope_id', $this->premises_id)
            ->orderByDesc('scheduled_at')
            ->get();
    }
}
