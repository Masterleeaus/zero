<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Premises\Premises;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An execution of a ChecklistTemplate in a given context.
 *
 * Polymorphic context (runnable): service_job | inspection_instance | premises
 *
 * Status values: pending | in_progress | completed | skipped

/**
 * ChecklistRun — an execution of a checklist template.
 *
 * Can be linked to a ServiceJob, an InspectionInstance, or a Premises directly.
 * Supports reuse of the same checklist template across different execution contexts.
 *
 * Status: pending | in_progress | completed | failed
 */
class ChecklistRun extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'checklist_runs';

    protected $fillable = [
        'company_id',
        'created_by',
        'checklist_template_id',
        'runnable_type',
        'runnable_id',
        'title',
        'status',
        'assigned_to',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'service_job_id',
        'inspection_instance_id',
        'premises_id',
        'checklist_id',
        'title',
        'status',
        'items_total',
        'items_completed',
        'items_failed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'items_total'     => 'integer',
        'items_completed' => 'integer',
        'items_failed'    => 'integer',
        'started_at'      => 'datetime',
        'completed_at'    => 'datetime',
    ];

    protected $attributes = [
        'status'          => 'pending',
        'items_total'     => 0,
        'items_completed' => 0,
        'items_failed'    => 0,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }

    public function runnable(): MorphTo
    {
        return $this->morphTo();
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ChecklistResponse::class, 'checklist_run_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function passRate(): float
    {
        $total = $this->responses()->count();
        if ($total === 0) {
            return 0.0;
        }

        $passed = $this->responses()->where('result', 'pass')->count();

        return round(($passed / $total) * 100, 1);
    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function inspectionInstance(): BelongsTo
    {
        return $this->belongsTo(InspectionInstance::class, 'inspection_instance_id');
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    /** Source checklist template (App\Models\Work\Checklist). */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function completionPercentage(): int
    {
        if ($this->items_total === 0) {
            return 0;
        }

        return (int) round(($this->items_completed / $this->items_total) * 100);
    }

    public function hasFailed(): bool
    {
        return $this->items_failed > 0 || $this->status === 'failed';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForJob(Builder $query, int $jobId): Builder
    {
        return $query->where('service_job_id', $jobId);
    }

    public function scopeForInspection(Builder $query, int $inspectionId): Builder
    {
        return $query->where('inspection_instance_id', $inspectionId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
