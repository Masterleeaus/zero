<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Crm\Customer;
use App\Models\Money\Quote;
use App\Models\Premises\Premises;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceAgreement extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $guarded = [];

    protected $casts = [
        'next_run_at'            => 'datetime',
        'expired_at'             => 'datetime',
        'has_equipment_coverage' => 'boolean',
        'recurring_plan_count'   => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scheduleNext(): void
    {
        if ($this->frequency && $this->next_run_at) {
            $this->next_run_at = $this->next_run_at->add($this->frequencyInterval());
            $this->save();
        }
    }

    public function createJob(array $attributes = []): ServiceJob
    {
        $data = array_merge([
            'company_id'   => $this->company_id,
            'customer_id'  => $this->customer_id,
            'site_id'      => $this->site_id,
            'agreement_id' => $this->id,
            'title'        => $attributes['title'] ?? 'Recurring service',
            'status'       => $attributes['status'] ?? 'scheduled',
            'scheduled_at' => $attributes['scheduled_at'] ?? $this->next_run_at,
        ], $attributes);

        return $this->jobs()->create($data);
    }

    protected function frequencyInterval(): \DateInterval
    {
        return match ($this->frequency) {
            'weekly' => new \DateInterval('P7D'),
            'monthly' => new \DateInterval('P1M'),
            'quarterly' => new \DateInterval('P3M'),
            default => new \DateInterval('P1M'),
        };
    }

    public function scopeNotPaused($query)
    {
        return $query->where('status', '!=', 'paused');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'agreement_id');
    }

    public function servicePlan(): HasOne
    {
        return $this->hasOne(ServicePlan::class, 'agreement_id');
    }

    public function servicePlans(): HasMany
    {
        return $this->hasMany(ServicePlan::class, 'agreement_id');
    }

    /**
     * All service plan visits linked to this agreement via its service plans.
     */
    public function visits(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(ServicePlanVisit::class, ServicePlan::class, 'agreement_id', 'service_plan_id');
    }

    /**
     * Whether this agreement is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // ── fieldservice_sale_agreement_equipment_stock helpers ──────────────────

    /**
     * All InstalledEquipment records covered by this agreement.
     *
     * Mirrors Odoo fieldservice_sale_agreement_equipment_stock:
     *   agreement → covered equipment instances.
     */
    public function installedEquipment(): HasMany
    {
        return $this->hasMany(\App\Models\Equipment\InstalledEquipment::class, 'agreement_id');
    }

    /**
     * Active installed equipment units covered by this agreement.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment\InstalledEquipment>
     */
    public function coveredEquipment(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->installedEquipment()
            ->where('status', 'active')
            ->whereNotNull('coverage_activated_at')
            ->where(static function ($q) {
                $q->whereNull('coverage_end_date')
                    ->orWhere('coverage_end_date', '>=', now()->toDateString());
            })
            ->with('equipment')
            ->get();
    }

    /**
     * Summary of recurring coverage across all active service plans.
     *
     * @return array{
     *     agreement_id: int,
     *     has_equipment_coverage: bool,
     *     active_plan_count: int,
     *     pending_visit_count: int,
     *     completed_visit_count: int,
     *     covered_equipment_count: int
     * }
     */
    public function recurringCoverageSummary(): array
    {
        return [
            'agreement_id'            => $this->id,
            'has_equipment_coverage'  => (bool) $this->has_equipment_coverage,
            'active_plan_count'       => $this->servicePlans()->where('is_active', true)->count(),
            'pending_visit_count'     => $this->visits()->whereIn('status', ['pending', 'scheduled'])->count(),
            'completed_visit_count'   => $this->visits()->where('status', 'completed')->count(),
            'covered_equipment_count' => $this->coveredEquipment()->count(),
        ];
    }

    /**
     * Coverage timeline: per-equipment coverage state and visit history.
     *
     * Delegates to EquipmentCoverageService for the full structured output.
     *
     * @return array<string, mixed>
     */
    public function coverageTimeline(): array
    {
        return app(\App\Services\Work\EquipmentCoverageService::class)
            ->coverageTimeline($this);
    }

    // ── fieldservice_sale_agreement helpers ───────────────────────────────────

    /**
     * The Quote that activated or originated this service agreement.
     *
     * Mirrors Odoo fieldservice_sale_agreement: agreement_id propagated from sale → fsm.order.
     * Checks originating_quote_id first, then quote_id.
     */
    public function originatingSale(): ?\App\Models\Money\Quote
    {
        if ($this->originating_quote_id) {
            return \App\Models\Money\Quote::find($this->originating_quote_id);
        }

        return $this->quote;
    }

    /**
     * Summary of service coverage sold through this agreement.
     *
     * @return array{
     *     agreement_id: int,
     *     status: string,
     *     originating_quote_id: int|null,
     *     total_jobs: int,
     *     completed_jobs: int,
     *     pending_jobs: int,
     *     total_visits: int,
     *     completed_visits: int
     * }
     */
    public function saleCoverageSummary(): array
    {
        return [
            'agreement_id'         => $this->id,
            'status'               => $this->status,
            'originating_quote_id' => $this->originating_quote_id ?? $this->quote_id,
            'total_jobs'           => $this->jobs()->count(),
            'completed_jobs'       => $this->jobs()->where('status', 'completed')->count(),
            'pending_jobs'         => $this->jobs()->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'total_visits'         => $this->visits()->count(),
            'completed_visits'     => $this->visits()->where('status', 'completed')->count(),
        ];
    }
}
