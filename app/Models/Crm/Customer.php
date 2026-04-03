<?php

declare(strict_types=1);

namespace App\Models\Crm;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\Premises\Premises;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlan;
use App\Models\Work\ServicePlanVisit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'email',
        'phone',
        'status',
        'notes',
        'team_id',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'customer_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Scope: billable completed jobs with no invoice yet.
     */
    public function scopeHasUnbilledJobs(Builder $query): Builder
    {
        return $query->whereHas('serviceJobs', function (Builder $q) {
            $q->where('is_billable', true)
              ->whereNull('invoice_id')
              ->where('status', 'completed');
        });
    }

    /**
     * Query helper: returns the unbilled completed service jobs for this customer.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ServiceJob>
     */
    public function unbilledJobs(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->serviceJobs()
            ->where('is_billable', true)
            ->whereNull('invoice_id')
            ->where('status', 'completed')
            ->orderBy('date_end')
            ->get();
    }

    /**
     * Query helper: returns all pending (todo) activities across all jobs for this customer.
     *
     * Ordered by follow_up_at ASC (nulls last), then sequence ASC.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Work\JobActivity>
     */
    public function pendingActivities(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Work\JobActivity::query()
            ->where('company_id', $this->company_id)
            ->where('state', 'todo')
            ->forCustomer($this->id)
            ->orderByRaw('follow_up_at IS NULL, follow_up_at ASC')
            ->orderBy('sequence')
            ->with(['job'])
            ->get();
    }

    // ── CRM Timeline Service Visibility ──────────────────────────────────────

    /**
     * Most recently completed service job for this customer.
     */
    public function latestServiceJob(): ?ServiceJob
    {
        return $this->serviceJobs()
            ->where('status', 'completed')
            ->latest('date_end')
            ->first();
    }

    /**
     * All open (non-completed, non-cancelled) service jobs.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ServiceJob>
     */
    public function openServiceJobs(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->serviceJobs()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('scheduled_date_start')
            ->get();
    }

    /** Date of the last completed service job, or null. */
    public function lastServiceDate(): ?\Carbon\Carbon
    {
        $max = $this->serviceJobs()
            ->where('status', 'completed')
            ->max('date_end');

        return $max ? \Carbon\Carbon::parse($max) : null;
    }

    /** Date of the next scheduled service job, or null. */
    public function nextServiceDate(): ?\Carbon\Carbon
    {
        $next = $this->serviceJobs()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('scheduled_date_start')
            ->where('scheduled_date_start', '>=', now())
            ->min('scheduled_date_start');

        return $next ? \Carbon\Carbon::parse($next) : null;
    }

    /**
     * Pending activities across all jobs that are required but not yet done.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Work\JobActivity>
     */
    public function pendingRequiredActivities(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Work\JobActivity::query()
            ->where('company_id', $this->company_id)
            ->where('state', 'todo')
            ->where('required', true)
            ->forCustomer($this->id)
            ->orderByRaw('follow_up_at IS NULL, follow_up_at ASC')
            ->get();
    }

    /**
     * Pending follow-up activities across all jobs.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Work\JobActivity>
     */
    public function pendingFollowups(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Work\JobActivity::query()
            ->where('company_id', $this->company_id)
            ->where('state', 'todo')
            ->forCustomer($this->id)
            ->whereNotNull('follow_up_at')
            ->orderBy('follow_up_at')
            ->get();
    }

    // ── Service Health Summary ────────────────────────────────────────────────

    /**
     * Returns a service health snapshot for dashboard usage.
     *
     * @return array{
     *     open_jobs: int,
     *     overdue_followups: int,
     *     unbilled_jobs: int,
     *     recent_failures: int,
     *     recent_cancellations: int,
     *     return_visits_required: int
     * }
     */
    public function serviceHealthSummary(): array
    {
        $baseJobs = $this->serviceJobs()->where('company_id', $this->company_id);

        $openJobs = (clone $baseJobs)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $overdueFollowups = \App\Models\Work\JobActivity::query()
            ->where('company_id', $this->company_id)
            ->where('state', 'todo')
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<', now())
            ->forCustomer($this->id)
            ->count();

        $unbilledJobs = (clone $baseJobs)
            ->where('is_billable', true)
            ->whereNull('invoice_id')
            ->where('status', 'completed')
            ->count();

        $recentFailures = (clone $baseJobs)
            ->whereIn('service_outcome', [
                ServiceJob::OUTCOME_NO_ACCESS,
                ServiceJob::OUTCOME_NO_SHOW,
                ServiceJob::OUTCOME_CANCELLED_INTERNAL,
            ])
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        $recentCancellations = (clone $baseJobs)
            ->whereIn('service_outcome', [
                ServiceJob::OUTCOME_CANCELLED_CUSTOMER,
                ServiceJob::OUTCOME_CANCELLED_INTERNAL,
            ])
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        $returnVisitsRequired = (clone $baseJobs)
            ->where('service_outcome', ServiceJob::OUTCOME_RETURN_VISIT_REQUIRED)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        return [
            'open_jobs'              => $openJobs,
            'overdue_followups'      => $overdueFollowups,
            'unbilled_jobs'          => $unbilledJobs,
            'recent_failures'        => $recentFailures,
            'recent_cancellations'   => $recentCancellations,
            'return_visits_required' => $returnVisitsRequired,
        ];
    }

    // ── Premises Relationship Helpers ─────────────────────────────────────────

    public function premises(): HasMany
    {
        return $this->hasMany(Premises::class, 'customer_id');
    }

    /**
     * The primary (first active) premises for this customer.
     */
    public function primaryPremises(): ?Premises
    {
        return $this->premises()->where('status', 'active')->oldest()->first();
    }

    /**
     * All active premises for this customer.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Premises>
     */
    public function activePremises(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->premises()->where('status', 'active')->get();
    }

    // ── Stage G — CRM Service Intelligence ───────────────────────────────────

    /**
     * Upcoming scheduled service visits across all premises for this customer.
     *
     * Returns ServicePlanVisit records that are pending or scheduled and not yet past.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ServicePlanVisit>
     */
    public function upcomingServiceVisits(): \Illuminate\Database\Eloquent\Collection
    {
        $premisesIds = $this->premises()->pluck('id');

        return ServicePlanVisit::query()
            ->whereHas('plan', fn ($q) => $q->whereIn('premises_id', $premisesIds)
                ->orWhere('customer_id', $this->id))
            ->whereIn('status', ['pending', 'scheduled'])
            ->where(function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNotNull('scheduled_date')
                          ->where('scheduled_date', '>=', now()->toDateString());
                })->orWhere(function ($inner) {
                    $inner->whereNotNull('scheduled_for')
                          ->where('scheduled_for', '>=', now());
                });
            })
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_for')
            ->get();
    }

    /**
     * Upcoming (not-yet-completed) inspections across all premises for this customer.
     *
     * Module 9 (fieldservice_calendar) — CRM calendar surface helper.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inspection\InspectionInstance>
     */
    public function upcomingInspections(): \Illuminate\Database\Eloquent\Collection
    {
        $premisesIds = $this->premises()->pluck('id');

        return \App\Models\Inspection\InspectionInstance::query()
            ->where(function ($q) use ($premisesIds) {
                $q->where(function ($inner) use ($premisesIds) {
                    $inner->where('scope_type', \App\Models\Premises\Premises::class)
                          ->whereIn('scope_id', $premisesIds);
                })->orWhereHas('serviceJob', fn ($j) => $j->where('customer_id', $this->id));
            })
            ->whereNotIn('status', ['completed', 'failed', 'cancelled'])
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '>=', now());
            })
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * Active hazards across all premises belonging to this customer.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Premises\Hazard>
     */
    public function activeHazards(): \Illuminate\Database\Eloquent\Collection
    {
        $premisesIds = $this->premises()->pluck('id');

        return \App\Models\Premises\Hazard::query()
            ->whereIn('premises_id', $premisesIds)
            ->where('status', 'active')
            ->orderBy('severity')
            ->get();
    }

    /**
     * Service plan coverage summary for this customer.
     *
     * Returns a snapshot of active plans, upcoming visits, and overdue visits
     * across all premises.
     *
     * @return array{
     *     active_plans: int,
     *     upcoming_visits: int,
     *     overdue_visits: int,
     *     premises_covered: int,
     *     premises_without_plan: int
     * }
     */
    public function servicePlanCoverageSummary(): array
    {
        $premisesIds = $this->premises()->pluck('id');

        $activePlans = ServicePlan::query()
            ->whereIn('premises_id', $premisesIds)
            ->where('is_active', true)
            ->count();

        $upcomingVisits = ServicePlanVisit::query()
            ->whereHas('plan', fn ($q) => $q->whereIn('premises_id', $premisesIds))
            ->whereIn('status', ['pending', 'scheduled'])
            ->where('scheduled_date', '>=', now()->toDateString())
            ->count();

        $overdueVisits = ServicePlanVisit::query()
            ->whereHas('plan', fn ($q) => $q->whereIn('premises_id', $premisesIds))
            ->whereIn('status', ['pending', 'scheduled'])
            ->where('scheduled_date', '<', now()->toDateString())
            ->count();

        $premisesCovered = ServicePlan::query()
            ->whereIn('premises_id', $premisesIds)
            ->where('is_active', true)
            ->distinct('premises_id')
            ->count('premises_id');

        return [
            'active_plans'           => $activePlans,
            'upcoming_visits'        => $upcomingVisits,
            'overdue_visits'         => $overdueVisits,
            'premises_covered'       => $premisesCovered,
            'premises_without_plan'  => max(0, $premisesIds->count() - $premisesCovered),
        ];
    }

    /**
     * Alias for servicePlanCoverageSummary() — standard CRM API name.
     *
     * @return array{
     *     active_plans: int,
     *     upcoming_visits: int,
     *     overdue_visits: int,
     *     premises_covered: int,
     *     premises_without_plan: int
     * }
     */
    public function serviceCoverageSummary(): array
    {
        return $this->servicePlanCoverageSummary();
    }

    /**
     * Full chronological timeline of service events for this customer.
     *
     * Merges ServiceJobs, ServicePlanVisits, InspectionInstances, and Hazards.
     * Ordered chronologically descending.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function timeline(): \Illuminate\Support\Collection
    {
        return app(\App\Services\Scheduling\CustomerTimelineAggregator::class)
            ->forCustomer($this);
    }

    /**
     * Inventory of all site assets across all premises for this customer.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Facility\SiteAsset>
     */
    public function assetInventory(): \Illuminate\Database\Eloquent\Collection
    {
        $premisesIds = $this->premises()->pluck('id');

        return \App\Models\Facility\SiteAsset::query()
            ->whereIn('premises_id', $premisesIds)
            ->where('status', 'active')
            ->orderBy('label')
            ->get();
    }

    /**
     * Risk profile snapshot for this customer.
     *
     * Aggregates hazard counts, severity distribution, and open inspection findings
     * across all premises.
     *
     * @return array{
     *     active_hazards: int,
     *     critical_hazards: int,
     *     high_hazards: int,
     *     medium_hazards: int,
     *     low_hazards: int,
     *     failed_inspections: int,
     *     open_followups: int,
     *     risk_level: string
     * }
     */
    public function riskProfile(): array
    {
        $premisesIds = $this->premises()->pluck('id');

        $baseHazards = \App\Models\Premises\Hazard::query()
            ->whereIn('premises_id', $premisesIds)
            ->where('status', 'active');

        $critical = (clone $baseHazards)->where('severity', 'critical')->count();
        $high     = (clone $baseHazards)->where('severity', 'high')->count();
        $medium   = (clone $baseHazards)->where('severity', 'medium')->count();
        $low      = (clone $baseHazards)->where('severity', 'low')->count();
        $total    = $critical + $high + $medium + $low;

        $failedInspections = \App\Models\Inspection\InspectionInstance::query()
            ->where(function ($q) use ($premisesIds) {
                $q->where('scope_type', \App\Models\Premises\Premises::class)
                  ->whereIn('scope_id', $premisesIds);
            })
            ->where('status', 'failed')
            ->count();

        $openFollowups = \App\Models\Inspection\InspectionInstance::query()
            ->where(function ($q) use ($premisesIds) {
                $q->where('scope_type', \App\Models\Premises\Premises::class)
                  ->whereIn('scope_id', $premisesIds);
            })
            ->where('followup_required', true)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $riskLevel = match (true) {
            $critical > 0              => 'critical',
            $high > 2                  => 'high',
            $high > 0 || $medium > 3   => 'medium',
            $total > 0                 => 'low',
            default                    => 'none',
        };

        return [
            'active_hazards'    => $total,
            'critical_hazards'  => $critical,
            'high_hazards'      => $high,
            'medium_hazards'    => $medium,
            'low_hazards'       => $low,
            'failed_inspections' => $failedInspections,
            'open_followups'    => $openFollowups,
            'risk_level'        => $riskLevel,
        ];
    }

    // ── Warranty helpers (Module 8) ───────────────────────────────────────────

    /**
     * All installed equipment for this customer that have an active warranty.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment\InstalledEquipment>
     */
    public function activeWarrantyAssets(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Equipment\InstalledEquipment::query()
            ->where('customer_id', $this->id)
            ->where('company_id', $this->company_id)
            ->whereNotNull('warranty_expiry')
            ->whereDate('warranty_expiry', '>', now()->toDateString())
            ->get();
    }

    /**
     * Installed equipment for this customer with warranties expiring within $days days.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment\InstalledEquipment>
     */
    public function expiringWarrantyAssets(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        $cutoff = now()->addDays($days)->toDateString();

        return \App\Models\Equipment\InstalledEquipment::query()
            ->where('customer_id', $this->id)
            ->where('company_id', $this->company_id)
            ->whereNotNull('warranty_expiry')
            ->whereDate('warranty_expiry', '>', now()->toDateString())
            ->whereDate('warranty_expiry', '<=', $cutoff)
            ->get();
    }

    /**
     * Open warranty claims for this customer.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment\WarrantyClaim>
     */
    public function openWarrantyClaims(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Equipment\WarrantyClaim::query()
            ->where('customer_id', $this->id)
            ->where('company_id', $this->company_id)
            ->open()
            ->get();
    }

    // ── fieldservice_sale helpers ─────────────────────────────────────────────

    /**
     * Service jobs for this customer that originated from an accepted quote.
     *
     * A "sold" job is one with a quote_id that is in accepted/approved status.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ServiceJob>
     */
    public function soldServiceJobs(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->serviceJobs()
            ->whereNotNull('quote_id')
            ->get();
    }

    /**
     * Service agreements for this customer that were created via a quote/sale.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Work\ServiceAgreement>
     */
    public function soldAgreements(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Work\ServiceAgreement::query()
            ->where('customer_id', $this->id)
            ->where('company_id', $this->company_id)
            ->where(function ($q) {
                $q->whereNotNull('quote_id')
                  ->orWhereNotNull('originating_quote_id');
            })
            ->get();
    }

    /**
     * Chronological timeline of quote-to-service transitions for this customer.
     *
     * Returns a merged view of:
     *   - accepted/approved quotes
     *   - service jobs linked to those quotes
     *   - service agreements linked to those quotes
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function saleToServiceTimeline(): \Illuminate\Support\Collection
    {
        $entries = collect();

        // Accepted quotes
        $quotes = $this->quotes()
            ->whereIn('status', ['accepted', 'approved', 'converted'])
            ->orderByDesc('issue_date')
            ->get();

        foreach ($quotes as $quote) {
            $entries->push([
                'type'        => 'quote_accepted',
                'entity_type' => Quote::class,
                'entity_id'   => $quote->id,
                'title'       => $quote->title ?? 'Quote #' . $quote->quote_number,
                'status'      => $quote->status,
                'date'        => $quote->issue_date?->toDateString(),
                'payload'     => ['quote_number' => $quote->quote_number, 'total' => $quote->total],
            ]);
        }

        // Service jobs linked to a quote
        $soldJobs = $this->soldServiceJobs();
        foreach ($soldJobs as $job) {
            $entries->push([
                'type'        => 'sale_converted_to_job',
                'entity_type' => ServiceJob::class,
                'entity_id'   => $job->id,
                'title'       => $job->title,
                'status'      => $job->status,
                'date'        => $job->scheduled_at?->toDateString() ?? $job->created_at?->toDateString(),
                'payload'     => ['quote_id' => $job->quote_id, 'sale_line_id' => $job->sale_line_id],
            ]);
        }

        // Agreements from quotes
        $soldAgreements = $this->soldAgreements();
        foreach ($soldAgreements as $agreement) {
            $entries->push([
                'type'        => 'sale_agreement_activated',
                'entity_type' => \App\Models\Work\ServiceAgreement::class,
                'entity_id'   => $agreement->id,
                'title'       => $agreement->title ?? 'Service Agreement #' . $agreement->id,
                'status'      => $agreement->status,
                'date'        => $agreement->created_at?->toDateString(),
                'payload'     => [
                    'quote_id'             => $agreement->quote_id,
                    'originating_quote_id' => $agreement->originating_quote_id,
                ],
            ]);
        }

        return $entries->sortByDesc('date')->values();
    }
}
