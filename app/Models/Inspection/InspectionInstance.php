<?php

declare(strict_types=1);

namespace App\Models\Inspection;

use App\Contracts\SchedulableEntity;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
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
}
