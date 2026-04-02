<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
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
    }
}
