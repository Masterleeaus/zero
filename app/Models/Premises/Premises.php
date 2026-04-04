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
use App\Models\Work\InspectionInstance;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlan;
use App\Models\Work\ServicePlanVisit;
use App\Models\Work\FieldServiceProject;
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

    public function projects(): HasMany
    {
        return $this->hasMany(FieldServiceProject::class, 'premises_id');
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
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(InspectionInstance::class, 'premises_id');
    }

    /**
     * All service agreements covering this premises.
     *
     * Inverse of ServiceAgreement::premises() BelongsTo.
     */
    public function serviceAgreements(): HasMany
    {
        return $this->hasMany(\App\Models\Work\ServiceAgreement::class, 'premises_id');
    }

    public function serviceVisits(): HasManyThrough
    {
        return $this->hasManyThrough(ServicePlanVisit::class, ServicePlan::class, 'premises_id', 'service_plan_id');
    }

    /**
     * All occupancies within units of this premises.
     *
     * Traverses the full Premises→Building→Floor→Unit→Occupancy hierarchy.
     * Returns a QueryBuilder (not an Eloquent relation) because the four-level
     * chain cannot be expressed as a single hasManyThrough.
     *
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Premises\Occupancy>
     */
    public function occupancies(): \Illuminate\Database\Eloquent\Builder
    {
        $unitIds = \App\Models\Premises\Unit::query()
            ->whereHas('floor', fn ($q) => $q->whereHas('building', fn ($b) => $b->where('premises_id', $this->id)))
            ->pluck('id');

        return \App\Models\Premises\Occupancy::query()
            ->whereIn('unit_id', $unitIds)
            ->where('company_id', $this->company_id);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Full chronological timeline of service events for this premises.
     *
     * Merges ServiceJobs, ServicePlanVisits, InspectionInstances, and Hazards.
     * Ordered chronologically descending.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function timeline(): \Illuminate\Support\Collection
    {
        return app(\App\Services\Scheduling\CustomerTimelineAggregator::class)
            ->forPremises($this);
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

    // ── Warranty helpers (Module 8) ───────────────────────────────────────────

    /**
     * All installed equipment at this premises that have an active warranty.
     *
     * Matches rows where warranty_status is explicitly 'active', OR where
     * warranty_status is not yet set but warranty_expiry is still in the future.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment\InstalledEquipment>
     */
    public function warrantyAssets(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->installedEquipment()
            ->where(function ($q) {
                $q->where('warranty_status', \App\Models\Equipment\EquipmentWarranty::STATUS_ACTIVE)
                  ->orWhere(function ($inner) {
                      $inner->whereNull('warranty_status')
                            ->whereNotNull('warranty_expiry')
                            ->whereDate('warranty_expiry', '>', now()->toDateString());
                  });
            })
            ->get();
    }

    /**
     * Installed equipment at this premises with warranties expiring within $days days.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment\InstalledEquipment>
     */
    public function expiringWarrantyAssets(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        $cutoff = now()->addDays($days)->toDateString();

        return $this->installedEquipment()
            ->whereNotNull('warranty_expiry')
            ->whereDate('warranty_expiry', '>', now()->toDateString())
            ->whereDate('warranty_expiry', '<=', $cutoff)
            ->get();
    }

    /**
     * Upcoming open service jobs for this premises.
     *
     * Module 9 (fieldservice_calendar) — premises calendar surface helper.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Work\ServiceJob>
     */
    public function upcomingServiceJobs(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->serviceJobs()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($q) {
                $q->whereNull('scheduled_date_start')
                  ->orWhere('scheduled_date_start', '>=', now());
            })
            ->orderBy('scheduled_date_start')
            ->get();
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

    // ── Repair relationships (Module 9) ───────────────────────────────────────

    /**
     * All repair orders at this premises.
     *
     * @return HasMany<\App\Models\Repair\RepairOrder>
     */
    public function repairOrders(): HasMany
    {
        return $this->hasMany(\App\Models\Repair\RepairOrder::class, 'premises_id');
    }

    /**
     * Open (non-terminal) repair orders at this premises.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Repair\RepairOrder>
     */
    public function openRepairs(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repairOrders()
            ->whereNotIn('repair_status', [
                \App\Models\Repair\RepairOrder::STATUS_COMPLETED,
                \App\Models\Repair\RepairOrder::STATUS_VERIFIED,
                \App\Models\Repair\RepairOrder::STATUS_CLOSED,
                \App\Models\Repair\RepairOrder::STATUS_CANCELLED,
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * All completed or closed repair orders (repair history) for this premises.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Repair\RepairOrder>
     */
    public function repairHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repairOrders()
            ->whereIn('repair_status', [
                \App\Models\Repair\RepairOrder::STATUS_COMPLETED,
                \App\Models\Repair\RepairOrder::STATUS_VERIFIED,
                \App\Models\Repair\RepairOrder::STATUS_CLOSED,
            ])
            ->orderByDesc('completed_at')
            ->get();
    }

    /**
     * Risk summary for repairs at this premises.
     *
     * Returns counts of open repairs by priority level.
     *
     * @return array{urgent: int, high: int, normal: int, low: int, total: int}
     */
    public function repairRiskSummary(): array
    {
        $open = $this->repairOrders()
            ->whereNotIn('repair_status', [
                \App\Models\Repair\RepairOrder::STATUS_COMPLETED,
                \App\Models\Repair\RepairOrder::STATUS_VERIFIED,
                \App\Models\Repair\RepairOrder::STATUS_CLOSED,
                \App\Models\Repair\RepairOrder::STATUS_CANCELLED,
            ])
            ->get(['priority']);

        return [
            'urgent' => $open->where('priority', 'urgent')->count(),
            'high'   => $open->where('priority', 'high')->count(),
            'normal' => $open->where('priority', 'normal')->count(),
            'low'    => $open->where('priority', 'low')->count(),
            'total'  => $open->count(),
        ];
    }

    // ── Portal helpers (Module 21 — fieldservice_portal) ─────────────────────

    /**
     * Upcoming service jobs and plan visits for this premises ordered by date.
     *
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function upcomingPortalEvents(int $limit = 10): \Illuminate\Support\Collection
    {
        $jobs = $this->serviceJobs()
            ->whereHas('stage', fn ($q) => $q->where('portal_visible', true))
            ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
            ->whereNotNull('scheduled_date_start')
            ->where('scheduled_date_start', '>=', now()->toDateString())
            ->orderBy('scheduled_date_start')
            ->limit($limit)
            ->get()
            ->map(fn ($j) => array_merge($j->toPortalCard(), ['event_type' => 'job']));

        $visits = $this->serviceVisits()
            ->whereIn('status', ['pending', 'scheduled'])
            ->whereNotNull('scheduled_date')
            ->where('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->limit($limit)
            ->get()
            ->map(fn ($v) => array_merge($v->toPortalCard(), ['event_type' => 'visit']));

        return $jobs->concat($visits)->sortBy('schedule')->take($limit)->values();
    }

    /**
     * Summary of portal-relevant service data for this premises.
     *
     * @return array<string, mixed>
     */
    public function portalServiceSummary(): array
    {
        return [
            'premises_id'      => $this->id,
            'name'             => $this->name,
            'open_jobs'        => $this->serviceJobs()->whereNotIn('status', ['completed', 'closed', 'cancelled'])->count(),
            'completed_jobs'   => $this->serviceJobs()->whereIn('status', ['completed', 'closed'])->count(),
            'active_projects'  => $this->projects()->where('status', 'active')->count(),
            'active_plans'     => $this->servicePlans()->where('status', 'active')->count(),
            'installed_assets' => $this->installedEquipment()->count(),
        ];
    }

    // ── fieldservice_sale_recurring_agreement helpers ─────────────────────────

    /**
     * Upcoming service plan visits for this premises that originated from sale-backed
     * recurring plans (sale_originated = true).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Work\ServicePlanVisit>
     */
    public function upcomingCoveredRecurringVisits(): \Illuminate\Database\Eloquent\Collection
    {
        $planIds = \App\Models\Work\ServicePlan::query()
            ->where('premises_id', $this->id)
            ->where('company_id', $this->company_id)
            ->where('originated_from_sale', true)
            ->where('status', 'active')
            ->pluck('id');

        if ($planIds->isEmpty()) {
            return \App\Models\Work\ServicePlanVisit::query()->whereRaw('1=0')->get();
        }

        return \App\Models\Work\ServicePlanVisit::query()
            ->whereIn('service_plan_id', $planIds)
            ->where('sale_originated', true)
            ->whereIn('status', ['pending', 'scheduled'])
            ->where('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->get();
    }

    /**
     * All sale-backed recurring service plans active at this premises.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Work\ServicePlan>
     */
    public function recurringCoveredVisitsBySale(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Work\ServicePlan::query()
            ->where('premises_id', $this->id)
            ->where('company_id', $this->company_id)
            ->where('originated_from_sale', true)
            ->where('status', 'active')
            ->orderByDesc('commercial_start_date')
            ->get();
    }
}
