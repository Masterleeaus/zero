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
}
