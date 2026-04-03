<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Contracts\SchedulableEntity;
use App\Events\Work\ServicePlanVisitDispatched;
use App\Events\Work\ServicePlanVisitScheduled;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * ServicePlanVisit — an individual scheduled occurrence within a ServicePlan.
 *
 * When dispatched, the visit is linked to a ServiceJob that executes the work.
 *
 *   ServicePlan → ServicePlanVisit → ServiceJob
 *
 * Status: pending | scheduled | completed | skipped | cancelled
 */
class ServicePlanVisit extends Model implements SchedulableEntity
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'service_plan_visits';

    protected $fillable = [
        'company_id',
        'created_by',
        'service_plan_id',
        'service_job_id',
        'project_id',
        'visit_type',
        'scheduled_for',
        'scheduled_date',
        'assigned_to',
        'status',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_for'  => 'datetime',
        'completed_at'   => 'datetime',
        'scheduled_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id');
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(FieldServiceProject::class, 'project_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'scheduled'])
            ->where('scheduled_date', '>=', now()->toDateString());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasLinkedJob(): bool
    {
        return $this->service_job_id !== null;
    }

    /**
     * Generate a ServiceJob from this planned visit.
     *
     * Pulls context from the parent ServicePlan (premises, customer, agreement).
     * Fires ServicePlanVisitScheduled event.
     */
    public function generateJob(array $attributes = []): ServiceJob
    {
        $plan = $this->plan;

        $data = array_merge([
            'company_id'   => $this->company_id,
            'customer_id'  => $plan->customer_id,
            'premises_id'  => $plan->premises_id,
            'agreement_id' => $plan->agreement_id,
            'title'        => $attributes['title'] ?? ($plan->name . ' visit'),
            'status'       => $attributes['status'] ?? 'scheduled',
            'scheduled_at' => $this->scheduled_for,
        ], $attributes);

        $job = ServiceJob::create($data);

        $this->service_job_id = $job->id;
        $this->save();

        ServicePlanVisitScheduled::dispatch($this);

        return $job;
    }

    /**
     * Dispatch this visit as a ServiceJob.
     *
     * Creates the job if not already linked, marks this visit as scheduled,
     * and fires ServicePlanVisitDispatched event.
     *
     * Ensures agreement_id, premises_id, customer_id, and company_id propagate.
     */
    public function dispatch(array $jobAttributes = []): ServiceJob
    {
        if ($this->service_job_id && $this->serviceJob) {
            return $this->serviceJob;
        }

        $plan      = $this->plan;
        $agreement = $plan?->agreement;

        $data = array_merge([
            'company_id'           => $this->company_id,
            'customer_id'          => $plan?->customer_id ?? $agreement?->customer_id,
            'premises_id'          => $plan?->premises_id ?? $agreement?->premises_id,
            'agreement_id'         => $plan?->agreement_id ?? $agreement?->id,
            'title'                => $plan?->name ?? $plan?->title ?? 'Scheduled visit',
            'status'               => 'scheduled',
            'scheduled_date_start' => $this->scheduled_date ?? $this->scheduled_for?->toDateString(),
        ], $jobAttributes);

        $job = ServiceJob::create($data);

        $this->update([
            'service_job_id' => $job->id,
            'status'         => 'scheduled',
        ]);

        ServicePlanVisitDispatched::dispatch($this, $job);

        return $job;
    }

    // ── Calendar helpers ──────────────────────────────────────────────────────

    /**
     * Return a FullCalendar-compatible calendar event array.
     *
     * Module 9 (fieldservice_calendar) — calendar display helper.
     *
     * @return array<string, mixed>
     */
    public function toCalendarEvent(): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->calendarTitle(),
            'start'         => $this->getScheduledStart(),
            'end'           => null,
            'color'         => '#22c55e',    // green-500 — visit colour
            'extendedProps' => $this->calendarMeta(),
        ];
    }

    /**
     * Human-readable calendar event title for this visit.
     *
     * Module 9 (fieldservice_calendar) — calendar display helper.
     */
    public function calendarTitle(): string
    {
        $planName = $this->plan?->name ?? ('Visit #' . $this->id);

        return '[Visit] ' . $planName;
    }

    /**
     * Extended calendar metadata for tooltip / detail rendering.
     *
     * Module 9 (fieldservice_calendar) — calendar display helper.
     *
     * @return array<string, mixed>
     */
    public function calendarMeta(): array
    {
        return [
            'type'           => 'service_plan_visit',
            'status'         => $this->status,
            'visit_type'     => $this->visit_type,
            'assignee_id'    => $this->assigned_to,
            'plan_id'        => $this->service_plan_id,
            'service_job_id' => $this->service_job_id,
            'is_dispatched'  => $this->hasLinkedJob(),
            'premises_id'    => $this->plan?->premises_id,
            'customer_id'    => $this->plan?->customer_id,
        ];
    }

    // ── SchedulableEntity contract ────────────────────────────────────────────

    public function getScheduledStart(): ?string
    {
        return $this->scheduled_for?->toIso8601String()
            ?? ($this->scheduled_date ? \Carbon\Carbon::parse($this->scheduled_date)->toIso8601String() : null);
    }

    public function getScheduledEnd(): ?string
    {
        return null;
    }

    public function getAssignedUserId(): ?int
    {
        return $this->assigned_to;
    }

    public function getSchedulableStatus(): string
    {
        return $this->status ?? 'pending';
    }

    public function getSchedulablePriority(): string|int|null
    {
        return null;
    }

    public function getSchedulableTitle(): string
    {
        return $this->plan?->name ?? 'Service Visit #' . $this->id;
    }

    public function getSchedulableType(): string
    {
        return static::class;
    }

    // ── Portal helpers (Module 21 — fieldservice_portal) ─────────────────────

    public function toPortalCard(): array
    {
        return [
            'id'       => $this->id,
            'title'    => $this->getSchedulableTitle(),
            'status'   => $this->portalStatusLabel(),
            'schedule' => $this->portalScheduleLabel(),
            'type'     => $this->visit_type ?? 'service',
        ];
    }

    public function portalStatusLabel(): string
    {
        return match ($this->status) {
            'pending'    => 'Upcoming',
            'scheduled'  => 'Scheduled',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
            default      => ucfirst((string) $this->status),
        };
    }

    public function portalScheduleLabel(): string
    {
        if ($this->scheduled_date) {
            return \Illuminate\Support\Carbon::parse($this->scheduled_date)->format('d M Y');
        }
        if ($this->scheduled_for) {
            return \Illuminate\Support\Carbon::parse($this->scheduled_for)->format('d M Y');
        }
        return 'To be confirmed';
    }
}
