<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Contracts\SchedulableEntity;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Deal;
use App\Models\Crm\Enquiry;
use App\Models\Equipment\Equipment;
use App\Models\Equipment\EquipmentMovement;
use App\Models\Equipment\InstalledEquipment;
use App\Models\Equipment\WarrantyClaim;
use App\Models\Money\Invoice;
use App\Models\Premises\Premises;
use App\Models\User;
use App\Models\Team\Team;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Repair\RepairOrder;

class ServiceJob extends Model implements SchedulableEntity
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    // ── Service Outcome constants ────────────────────────────────────────────

    public const OUTCOME_COMPLETED_SUCCESSFULLY        = 'completed_successfully';
    public const OUTCOME_COMPLETED_WITH_FOLLOWUP       = 'completed_with_followup_required';
    public const OUTCOME_COMPLETED_PARTIAL             = 'completed_partial';
    public const OUTCOME_CANCELLED_CUSTOMER            = 'cancelled_customer_request';
    public const OUTCOME_CANCELLED_INTERNAL            = 'cancelled_internal';
    public const OUTCOME_NO_ACCESS                     = 'no_access';
    public const OUTCOME_NO_SHOW                       = 'no_show';
    public const OUTCOME_RESCHEDULE_REQUIRED           = 'reschedule_required';
    public const OUTCOME_QUOTE_REQUIRED                = 'quote_required_after_visit';
    public const OUTCOME_RETURN_VISIT_REQUIRED         = 'return_visit_required';
    public const OUTCOME_AGREEMENT_REQUIRED            = 'agreement_required_after_visit';

    /** All valid outcome values. */
    public const OUTCOMES = [
        self::OUTCOME_COMPLETED_SUCCESSFULLY,
        self::OUTCOME_COMPLETED_WITH_FOLLOWUP,
        self::OUTCOME_COMPLETED_PARTIAL,
        self::OUTCOME_CANCELLED_CUSTOMER,
        self::OUTCOME_CANCELLED_INTERNAL,
        self::OUTCOME_NO_ACCESS,
        self::OUTCOME_NO_SHOW,
        self::OUTCOME_RESCHEDULE_REQUIRED,
        self::OUTCOME_QUOTE_REQUIRED,
        self::OUTCOME_RETURN_VISIT_REQUIRED,
        self::OUTCOME_AGREEMENT_REQUIRED,
    ];

    protected $fillable = [
        'company_id',
        'created_by',
        'team_id',
        'site_id',
        'premises_id',
        'customer_id',
        'enquiry_id',
        'deal_id',
        'quote_id',
        'agreement_id',
        'assigned_user_id',
        'stage_id',
        'job_type_id',
        'template_id',
        'title',
        'status',
        'service_outcome',
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
        // Module 8 — warranty linkage
        'is_warranty_job',
        'warranty_claim_id',
        'covered_equipment_id',
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
        'is_warranty_job'      => 'boolean',
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
        'is_warranty_job'    => false,
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

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
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

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'service_job_id');
    }

    public function installedEquipment(): HasMany
    {
        return $this->hasMany(InstalledEquipment::class, 'service_job_id');
    }

    public function equipmentMovements(): HasMany
    {
        return $this->hasMany(EquipmentMovement::class, 'service_job_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(InspectionInstance::class, 'service_job_id');
    }

    public function checklistRuns(): HasMany
    {
        return $this->hasMany(ChecklistRun::class, 'runnable_id')
            ->where('runnable_type', self::class);
    }

    public function planVisit(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ServicePlanVisit::class, 'service_job_id');
    }

    public function warrantyClaim(): BelongsTo
    {
        return $this->belongsTo(WarrantyClaim::class, 'warranty_claim_id');
    }

    public function coveredEquipment(): BelongsTo
    {
        return $this->belongsTo(InstalledEquipment::class, 'covered_equipment_id');
    }

    // ── Outcome helpers ───────────────────────────────────────────────────────

    /**
     * Record a structured service outcome on this job.
     *
     * Does NOT change the `status` column — outcome augments lifecycle
     * interpretation only.
     */
    public function recordOutcome(string $outcome): void
    {
        if (! in_array($outcome, self::OUTCOMES, true)) {
            throw new \InvalidArgumentException("Invalid service_outcome: {$outcome}");
        }

        $this->update(['service_outcome' => $outcome]);
    }

    public function requiresFollowUp(): bool
    {
        return in_array($this->service_outcome, [
            self::OUTCOME_COMPLETED_WITH_FOLLOWUP,
            self::OUTCOME_RETURN_VISIT_REQUIRED,
            self::OUTCOME_NO_ACCESS,
            self::OUTCOME_NO_SHOW,
            self::OUTCOME_RESCHEDULE_REQUIRED,
        ], true);
    }

    public function requiresQuote(): bool
    {
        return $this->service_outcome === self::OUTCOME_QUOTE_REQUIRED;
    }

    public function requiresAgreement(): bool
    {
        return $this->service_outcome === self::OUTCOME_AGREEMENT_REQUIRED;
    }

    public function isSuccessfulCompletion(): bool
    {
        return $this->service_outcome === self::OUTCOME_COMPLETED_SUCCESSFULLY;
    }

    /**
     * Derive CRM pipeline signal from the current service_outcome.
     *
     * Returns one of the crm_* event names or null when no signal applies.
     */
    public function crmSignal(): ?string
    {
        return match ($this->service_outcome) {
            self::OUTCOME_COMPLETED_SUCCESSFULLY  => 'crm_service_completed',
            self::OUTCOME_COMPLETED_WITH_FOLLOWUP => 'crm_followup_required',
            self::OUTCOME_RETURN_VISIT_REQUIRED   => 'crm_return_visit_required',
            self::OUTCOME_QUOTE_REQUIRED          => 'crm_quote_required',
            self::OUTCOME_AGREEMENT_REQUIRED      => 'crm_agreement_candidate',
            self::OUTCOME_NO_ACCESS,
            self::OUTCOME_NO_SHOW                 => 'crm_return_visit_required',
            default                               => null,
        };
    }

    /**
     * Detect post-service sales signals from the outcome and job context.
     *
     * Returns an array of signal names that should be emitted.
     *
     * @return list<string>
     */
    public function postServiceSalesSignals(): array
    {
        $signals = [];

        if ($this->service_outcome === self::OUTCOME_QUOTE_REQUIRED) {
            $signals[] = 'crm_upsell_detected';
        }

        if ($this->service_outcome === self::OUTCOME_AGREEMENT_REQUIRED) {
            $signals[] = 'crm_agreement_candidate';
            $signals[] = 'crm_recurring_candidate';
        }

        if ($this->service_outcome === self::OUTCOME_RETURN_VISIT_REQUIRED) {
            $signals[] = 'crm_repair_detected';
        }

        if ($this->service_outcome === self::OUTCOME_COMPLETED_WITH_FOLLOWUP) {
            $signals[] = 'crm_followup_required';
        }

        return $signals;
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
     * Stage G — Active hazards for the premises associated with this job.
     * Returns hazards scoped to premises_id when available.
     */
    public function siteHazards(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->premises_id) {
            return collect();
        }

        return \App\Models\Premises\Hazard::where('premises_id', $this->premises_id)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Site access profile for this job's premises.
     */
    public function siteAccessProfile(): ?\App\Models\Premises\SiteAccessProfile
    {
        if (! $this->premises_id) {
            return null;
        }

        return $this->premises?->activeSiteAccess();
    }

    // ── Warranty helpers (Module 8) ───────────────────────────────────────────

    /** Whether this job is classified as warranty work. */
    public function isWarrantyWork(): bool
    {
        return (bool) $this->is_warranty_job;
    }

    /** Whether this job is linked to an active warranty claim. */
    public function coveredByWarranty(): bool
    {
        return $this->is_warranty_job && $this->warranty_claim_id !== null;
    }

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
     *  - 'date_and_time'  → "27/04/2025 15:30 - 17:00"  (or cross-day variant)
     *
     * Dates are presented in the application timezone (config('app.timezone')).
     * Returns null when scheduled_date_start is not set.
     */
    public function getScheduleTimeRangeAttribute(): ?string
    {
        if (! $this->scheduled_date_start) {
            return null;
        }

        $tz     = config('app.timezone', 'UTC');
        $format = config('workcore.schedule_time_range_format', 'time_only');

        $start = $this->scheduled_date_start->copy()->setTimezone($tz);
        $end   = $this->scheduled_date_end?->copy()->setTimezone($tz);

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
     * Return scheduled duration as a human-readable label, e.g. "2h 30m".
     *
     * Falls back to actual duration when the job has started/ended.
     * Returns null when no duration is recorded.
     *
     * Module 5 (fieldservice_kanban_info) — kanban-friendly duration helper.
     */
    public function getScheduledDurationFormattedAttribute(): ?string
    {
        $hours = $this->duration;

        if ($hours <= 0) {
            return null;
        }

        $totalMinutes = (int) round($hours * 60);
        $h            = intdiv($totalMinutes, 60);
        $m            = $totalMinutes % 60;

        if ($h > 0 && $m > 0) {
            return "{$h}h {$m}m";
        }

        if ($h > 0) {
            return "{$h}h";
        }

        return "{$m}m";
    }

    /**
     * Return a contextual window label for the scheduled start date.
     *
     * Returns "Today", "Tomorrow", or a formatted date string.
     * Useful for grouping jobs on the dispatch board and calendar view.
     *
     * Module 5 (fieldservice_kanban_info) — scheduled window helper.
     */
    public function getScheduledWindowLabelAttribute(): ?string
    {
        if (! $this->scheduled_date_start) {
            return null;
        }

        $tz    = config('app.timezone', 'UTC');
        $start = $this->scheduled_date_start->copy()->setTimezone($tz);
        $today = Carbon::today($tz);

        if ($start->isSameDay($today)) {
            return 'Today';
        }

        if ($start->isSameDay($today->copy()->addDay())) {
            return 'Tomorrow';
        }

        return $start->format('d/m/Y');
    }

    /**
     * Return a compact array of card metadata for kanban/dispatch board rendering.
     *
     * Module 5 (fieldservice_kanban_info) — board card summary.
     *
     * @return array<string, mixed>
     */
    public function boardSummary(): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'status'           => $this->status,
            'priority'         => $this->priority,
            'schedule_range'   => $this->schedule_time_range,
            'duration'         => $this->scheduled_duration_formatted,
            'window_label'     => $this->scheduled_window_label,
            'assignee'         => $this->assignedUser?->name,
            'customer'         => $this->customer?->name,
            'site'             => $this->site?->name,
            'stage'            => $this->stage?->name,
            'is_overdue'       => $this->scheduled_date_end && $this->scheduled_date_end->isPast()
                                   && ! in_array($this->status, ['completed', 'cancelled'], true),
            'needs_signature'  => $this->require_signature && $this->signed_on === null,
            'is_dispatch_ready' => $this->isDispatchReady(),
            'enquiry_id'       => $this->enquiry_id,
            'deal_id'          => $this->deal_id,
        ];
    }

    /**
     * Return a calendar event array compatible with FullCalendar / generic calendar views.
     *
     * Module 5 (fieldservice_kanban_info) — calendar-view compatibility helper.
     *
     * @return array<string, mixed>
     */
    public function toCalendarEvent(): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->calendarTitle(),
            'start'         => $this->scheduled_date_start?->toIso8601String(),
            'end'           => $this->scheduled_date_end?->toIso8601String(),
            'color'         => $this->calendarColor(),
            'extendedProps' => $this->calendarMeta(),
        ];
    }

    /**
     * Human-readable calendar event title.
     *
     * Module 9 (fieldservice_calendar) — calendar display helper.
     */
    public function calendarTitle(): string
    {
        $base = $this->title ?? ('Job #' . $this->id);

        if ($customer = $this->customer?->name) {
            return $base . ' — ' . $customer;
        }

        return $base;
    }

    /**
     * Calendar event colour — stage colour if set, otherwise priority-based fallback.
     *
     * Module 9 (fieldservice_calendar) — calendar display helper.
     */
    public function calendarColor(): string
    {
        if ($stageColor = $this->stage?->color) {
            return $stageColor;
        }

        return match ($this->priority) {
            'urgent' => '#ef4444',   // red-500
            'high'   => '#f97316',   // orange-500
            'normal' => '#3b82f6',   // blue-500
            'low'    => '#6b7280',   // gray-500
            default  => '#3b82f6',
        };
    }

    /**
     * Extended calendar metadata for FullCalendar extendedProps / rich tooltip rendering.
     *
     * Module 9 (fieldservice_calendar) — calendar display helper.
     *
     * @return array<string, mixed>
     */
    public function calendarMeta(): array
    {
        return [
            'type'         => 'service_job',
            'status'       => $this->status,
            'priority'     => $this->priority,
            'assignee'     => $this->assignedUser?->name,
            'assignee_id'  => $this->assigned_user_id,
            'team'         => $this->team?->name,
            'team_id'      => $this->team_id,
            'customer'     => $this->customer?->name,
            'customer_id'  => $this->customer_id,
            'premises_id'  => $this->premises_id,
            'site'         => $this->site?->name,
            'duration'     => $this->scheduled_duration_formatted,
            'is_billable'  => $this->is_billable,
            'enquiry_id'   => $this->enquiry_id,
            'deal_id'      => $this->deal_id,
            'agreement_id' => $this->agreement_id,
        ];
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

    /**
     * Scope: jobs scheduled on today's date (app timezone).
     *
     * Module 5 (fieldservice_kanban_info) — scheduled window helper scope.
     */
    public function scopeScheduledToday(Builder $query): Builder
    {
        $tz = config('app.timezone', 'UTC');

        return $query->whereDate('scheduled_date_start', Carbon::today($tz)->toDateString());
    }

    /**
     * Scope: jobs scheduled on tomorrow's date (app timezone).
     *
     * Module 5 (fieldservice_kanban_info) — scheduled window helper scope.
     */
    public function scopeScheduledTomorrow(Builder $query): Builder
    {
        $tz = config('app.timezone', 'UTC');

        return $query->whereDate('scheduled_date_start', Carbon::tomorrow($tz)->toDateString());
    }

    /**
     * Scope: jobs scheduled within the current calendar week (Mon–Sun, app timezone).
     *
     * Module 5 (fieldservice_kanban_info) — scheduled window helper scope.
     */
    public function scopeScheduledThisWeek(Builder $query): Builder
    {
        $tz    = config('app.timezone', 'UTC');
        $start = Carbon::now($tz)->startOfWeek();
        $end   = Carbon::now($tz)->endOfWeek();

        return $query->whereBetween('scheduled_date_start', [$start, $end]);
    }

    /**
     * Scope: primary dispatch board sort — priority desc, then scheduled_date_start asc.
     *
     * Priority order: urgent > high > normal > low.
     *
     * Module 5 (fieldservice_kanban_info) — dispatch board compatibility helper.
     */
    public function scopeSortedForDispatch(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderBy('scheduled_date_start');
    }

    /**
     * Scope: jobs linked to a specific enquiry (CRM lead).
     *
     * Module 6 (fieldservice_crm) — CRM linkage scope.
     */
    public function scopeForEnquiry(Builder $query, int $enquiryId): Builder
    {
        return $query->where('enquiry_id', $enquiryId);
    }

    /**
     * Scope: jobs linked to a specific deal (CRM opportunity).
     *
     * Module 6 (fieldservice_crm) — CRM linkage scope.
     */
    public function scopeForDeal(Builder $query, int $dealId): Builder
    {
        return $query->where('deal_id', $dealId);
    }

    /**
     * Scope: jobs linked to a specific customer.
     */
    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: jobs linked to a specific premises.
     */
    public function scopeForPremises(Builder $query, int $premisesId): Builder
    {
        return $query->where('premises_id', $premisesId);
    }

    /**
     * Scope: jobs linked to a specific service agreement.
     */
    public function scopeForAgreement(Builder $query, int $agreementId): Builder
    {
        return $query->where('agreement_id', $agreementId);
    }

    /** Scope: jobs flagged as warranty work. */
    public function scopeWarrantyJobs(Builder $query): Builder
    {
        return $query->where('is_warranty_job', true);
    }

    // ── SchedulableEntity contract ────────────────────────────────────────────

    public function getScheduledStart(): ?string
    {
        return $this->scheduled_date_start?->toIso8601String();
    }

    public function getScheduledEnd(): ?string
    {
        return $this->scheduled_date_end?->toIso8601String();
    }

    public function getAssignedUserId(): ?int
    {
        return $this->assigned_user_id;
    }

    public function getSchedulableStatus(): string
    {
        return $this->status ?? 'scheduled';
    }

    public function getSchedulablePriority(): string|int|null
    {
        return $this->priority;
    }

    public function getSchedulableTitle(): string
    {
        return $this->title ?? 'Service Job #' . $this->id;
    }

    public function getSchedulableType(): string
    {
        return static::class;
    }

    // ── Repair relationships (Module 9) ───────────────────────────────────────

    /**
     * All repair orders that originated from this service job.
     *
     * @return HasMany<RepairOrder>
     */
    public function repairOrders(): HasMany
    {
        return $this->hasMany(RepairOrder::class, 'service_job_id');
    }
}
