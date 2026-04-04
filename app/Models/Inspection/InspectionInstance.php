<?php

declare(strict_types=1);

namespace App\Models\Inspection;

use App\Contracts\SchedulableEntity;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Premises\InspectionInjectedDocument;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An individual inspection occurrence.
 *
 * Can be standalone, schedule-driven, or job-linked.
 * Scoped to a Premises, Building, Unit, or SiteAsset.
 *
 * Status values: scheduled | in_progress | completed | failed | cancelled
 */
class InspectionInstance extends Model implements SchedulableEntity
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'inspection_instances';

    protected $fillable = [
        'company_id',
        'created_by',
        'inspection_template_id',
        'inspection_schedule_id',
        'scope_type',
        'scope_id',
        'service_job_id',
        'inspection_type',
        'title',
        'status',
        'inspector_id',
        'assigned_to',
        'scheduled_at',
        'started_at',
        'completed_at',
        'score',
        'findings',
        'notes',
        'followup_required',
        'followup_notes',
    ];

    protected $casts = [
        'scheduled_at'     => 'datetime',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'findings'         => 'array',
        'followup_required' => 'boolean',
        'score'            => 'integer',
    ];

    protected $attributes = [
        'status'           => 'scheduled',
        'followup_required' => false,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(InspectionSchedule::class, 'inspection_schedule_id');
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InspectionItem::class, 'inspection_instance_id')
            ->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(InspectionResponse::class, 'inspection_instance_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(InspectionAttachment::class, 'inspection_instance_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeFollowupRequired(Builder $query): Builder
    {
        return $query->where('followup_required', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
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
            'end'           => $this->completed_at?->toIso8601String(),
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
        $base = $this->title ?? ('Inspection #' . $this->id);

        return '[Inspection] ' . $base;
    }

    /**
     * Calendar colour — varies by inspection type.
     *
     * Module 9 (fieldservice_calendar) — calendar display helper.
     */
    public function calendarColor(): string
    {
        return match ($this->inspection_type) {
            'compliance' => '#ef4444',   // red-500
            'safety'     => '#f97316',   // orange-500
            'handover'   => '#8b5cf6',   // violet-500
            'quality'    => '#0ea5e9',   // sky-500
            default      => '#f97316',   // orange-500 — default inspection colour
        };
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
            'type'              => 'inspection',
            'inspection_type'   => $this->inspection_type,
            'status'            => $this->status,
            'assignee_id'       => $this->assigned_to ?? $this->inspector_id,
            'service_job_id'    => $this->service_job_id,
            'schedule_id'       => $this->inspection_schedule_id,
            'followup_required' => $this->followup_required,
            'score'             => $this->score,
            'scope_type'        => $this->scope_type,
            'scope_id'          => $this->scope_id,
        ];
    }

    // ── SchedulableEntity contract ────────────────────────────────────────────

    public function getScheduledStart(): ?string
    {
        return $this->scheduled_at?->toIso8601String();
    }

    public function getScheduledEnd(): ?string
    {
        return null;
    }

    public function getAssignedUserId(): ?int
    {
        return $this->assigned_to ?? $this->inspector_id;
    }

    public function getSchedulableStatus(): string
    {
        return $this->status ?? 'scheduled';
    }

    public function getSchedulablePriority(): string|int|null
    {
        return null;
    }

    public function getSchedulableTitle(): string
    {
        return $this->title ?? 'Inspection #' . $this->id;
    }

    public function getSchedulableType(): string
    {
        return static::class;
    }

    // ── MODULE 08 — DocsExecutionBridge ───────────────────────────────────────

    public function injectedDocuments(): HasMany
    {
        return $this->hasMany(InspectionInjectedDocument::class, 'inspection_instance_id');
    }
}
