<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Money\Invoice;
use App\Models\User;
use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceJob extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'team_id',
        'site_id',
        'customer_id',
        'quote_id',
        'agreement_id',
        'assigned_user_id',
        'stage_id',
        'job_type_id',
        'template_id',
        'title',
        'status',
        'priority',
        'sequence',
        'territory_id',
        'branch_id',
        'district_id',
        'scheduled_at',
        'scheduled_date_start',
        'scheduled_duration',
        'scheduled_date_end',
        'date_start',
        'date_end',
        'notes',
        'todo',
        'resolution',
        'signed_by',
        'signed_on',
        'require_signature',
        'is_billable',
        'billable_rate',
        'invoice_id',
        'invoiced_at',
    ];

    protected $casts = [
        'scheduled_at'         => 'datetime',
        'scheduled_date_start' => 'datetime',
        'scheduled_date_end'   => 'datetime',
        'date_start'           => 'datetime',
        'date_end'             => 'datetime',
        'signed_on'            => 'datetime',
        'invoiced_at'          => 'datetime',
        'require_signature'    => 'boolean',
        'is_billable'          => 'boolean',
        'scheduled_duration'   => 'float',
        'billable_rate'        => 'decimal:2',
        'sequence'             => 'integer',
    ];

    protected $attributes = [
        'status'             => 'scheduled',
        'priority'           => 'normal',
        'sequence'           => 10,
        'require_signature'  => false,
        'is_billable'        => false,
        'scheduled_duration' => 0,
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Crm\Customer::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Money\Quote::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(JobStage::class, 'stage_id');
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class, 'job_type_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(JobTemplate::class, 'template_id');
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class, 'service_job_id');
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'service_job_workers', 'service_job_id', 'user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(JobActivity::class, 'service_job_id');
    }

    /**
     * Return activities for this job in timeline order (sequence ASC, created_at ASC).
     *
     * Eager-loads the completing user and assigned user for display purposes.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, JobActivity>
     */
    public function activityTimeline(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->activities()
            ->with(['completedByUser', 'assignedUser', 'team'])
            ->orderBy('sequence')
            ->orderBy('created_at')
            ->get();
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    public function getDurationAttribute(): float
    {
        if ($this->date_start && $this->date_end) {
            return round($this->date_start->diffInMinutes($this->date_end) / 60, 2);
        }

        return (float) $this->scheduled_duration;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Assign a technician as the primary assigned user.
     *
     * Emits JobAssigned event via the model-save observer path.
     */
    public function assignTechnician(\App\Models\User $user): void
    {
        $this->update(['assigned_user_id' => $user->id]);
    }

    /**
     * Record the actual start of work.
     */
    public function markStarted(): void
    {
        $this->update([
            'status'     => 'in_progress',
            'date_start' => $this->date_start ?? now(),
        ]);
    }

    /**
     * Record the actual completion of work.
     */
    public function markCompleted(): void
    {
        $this->update([
            'status'   => 'completed',
            'date_end' => $this->date_end ?? now(),
        ]);
    }

    /**
     * Capture a customer signature.
     */
    public function captureSignature(string $signedBy): void
    {
        $this->update([
            'signed_by' => $signedBy,
            'signed_on' => now(),
        ]);
    }

    /**
     * Normalise an arbitrary priority string to the accepted set.
     * Unknown values default to 'normal'.
     */
    public static function normalizePriority(string $priority): string
    {
        return in_array($priority, ['low', 'normal', 'high'], true) ? $priority : 'normal';
    }

    /**
     * Determine whether the job is ready to be dispatched.
     *
     * A job is dispatch-ready when it has a site, an assigned user,
     * and a scheduled start time.
     */
    public function isDispatchReady(): bool
    {
        return $this->site_id !== null
            && $this->assigned_user_id !== null
            && $this->scheduled_date_start !== null;
    }

    /**
     * Return the formatted schedule time range for kanban/card display.
     *
     * Module 5 (fieldservice_kanban_info) — mirrors Odoo's schedule_time_range
     * computed field, adapted to host timezone and locale conventions.
     *
     * Format is controlled by config('workcore.schedule_time_range_format'):
     *  - 'time_only'      → "15:30 - 17:00"  (default)
     *  - 'date_and_time'  → "02/27/2025 15:30 - 17:00"  (or cross-day variant)
     *
     * Returns null when scheduled_date_start is not set.
     */
    public function getScheduleTimeRangeAttribute(): ?string
    {
        if (! $this->scheduled_date_start) {
            return null;
        }

        $format = config('workcore.schedule_time_range_format', 'time_only');

        $start = $this->scheduled_date_start;
        $end   = $this->scheduled_date_end;

        $timeFormat = 'H:i';
        $dateFormat = 'd/m/Y';

        if ($format === 'date_and_time') {
            if ($end && $start->toDateString() === $end->toDateString()) {
                return $start->format($dateFormat . ' ' . $timeFormat) . ' - ' . $end->format($timeFormat);
            }

            if ($end) {
                return $start->format($dateFormat . ' ' . $timeFormat) . ' - ' . $end->format($dateFormat . ' ' . $timeFormat);
            }

            return $start->format($dateFormat . ' ' . $timeFormat);
        }

        // Default: time_only
        if ($end) {
            return $start->format($timeFormat) . ' - ' . $end->format($timeFormat);
        }

        return $start->format($timeFormat);
    }

    /**
     * Determine whether an invoice can be generated for this job.
     *
     * Returns false if the job is not billable, already invoiced, or not yet complete.
     */
    public function canGenerateInvoice(): bool
    {
        return $this->is_billable
            && $this->invoice_id === null
            && $this->status === 'completed';
    }

    /**
     * Return a cost/revenue summary for this job.
     *
     * Revenue is based on the linked invoice total (or estimated from
     * billable_rate × duration when not yet invoiced). Labour cost is
     * approximated from duration; materials costs are left for the caller
     * to extend via additional line items.
     *
     * @return array{duration_hours: float, billable_rate: float, estimated_revenue: float, invoiced_total: float|null, invoice_status: string|null}
     */
    public function revenueSummary(): array
    {
        $duration  = $this->duration;
        $rate      = (float) ($this->billable_rate ?? 0);
        $estimated = round($duration * $rate, 2);

        $invoice = $this->invoice;

        return [
            'duration_hours'    => $duration,
            'billable_rate'     => $rate,
            'estimated_revenue' => $estimated,
            'invoiced_total'    => $invoice ? (float) $invoice->total : null,
            'invoice_status'    => $invoice?->status,
        ];
    }

    /**
     * Return whether all required activities on this job are done.
     *
     * If no activities exist, returns true (no blockage).
     */
    public function hasRequiredActivitiesDone(): bool
    {
        return ! $this->activities()
            ->where('required', true)
            ->where('state', 'todo')
            ->exists();
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull($query->qualifyColumn('assigned_user_id'));
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereIn('status', ['completed', 'cancelled']);
    }

    public function scopeForTerritory(Builder $query, int $territoryId): Builder
    {
        return $query->where('territory_id', $territoryId);
    }

    public function scopeNeedsSignature(Builder $query): Builder
    {
        return $query->where('require_signature', true)->whereNull('signed_on');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('scheduled_date_end', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeDispatchReady(Builder $query): Builder
    {
        return $query->whereNotNull('site_id')
            ->whereNotNull('assigned_user_id')
            ->whereNotNull('scheduled_date_start')
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeBillable(Builder $query): Builder
    {
        return $query->where('is_billable', true);
    }

    public function scopeUnbilled(Builder $query): Builder
    {
        return $query->where('is_billable', true)->whereNull('invoice_id');
    }

    public function scopeScheduledBetween(Builder $query, \DateTimeInterface $from, \DateTimeInterface $to): Builder
    {
        return $query->whereBetween('scheduled_date_start', [$from, $to]);
    }
}
