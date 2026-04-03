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
 * ChecklistRun — an execution of a checklist template.
 *
 * Polymorphic runnable context: ServiceJob | InspectionInstance | Premises | ServicePlanVisit
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
        'items_total',
        'items_completed',
        'items_failed',
        'started_at',
        'completed_at',
        'notes',
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

    /**
     * Polymorphic runnable target.
     *
     * Supported types: ServiceJob, InspectionInstance, Premises, ServicePlanVisit
     */
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

    public function scopeForJob(Builder $query, int $jobId): Builder
    {
        return $query->where('runnable_type', ServiceJob::class)
            ->where('runnable_id', $jobId);
    }

    public function scopeForInspection(Builder $query, int $inspectionId): Builder
    {
        return $query->where('runnable_type', InspectionInstance::class)
            ->where('runnable_id', $inspectionId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function hasFailed(): bool
    {
        return $this->items_failed > 0 || $this->status === 'failed';
    }

    public function passRate(): float
    {
        $total = $this->responses()->count();
        if ($total === 0) {
            return 0.0;
        }

        $passed = $this->responses()->where('result', 'pass')->count();

        return round(($passed / $total) * 100, 1);
    }

    public function completionPercentage(): int
    {
        if ($this->items_total === 0) {
            return 0;
        }

        return (int) round(($this->items_completed / $this->items_total) * 100);
    }
}
