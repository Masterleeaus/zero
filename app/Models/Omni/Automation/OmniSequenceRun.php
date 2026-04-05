<?php

declare(strict_types=1);

namespace App\Models\Omni\Automation;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Omni\OmniCustomer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OmniSequenceRun — Execution record tracking one customer through one sequence.
 *
 * Unique per (sequence_id, omni_customer_id) — a customer may only be running
 * a given sequence once at a time.
 *
 * @property int         $id
 * @property int         $sequence_id
 * @property int         $company_id
 * @property int         $omni_customer_id
 * @property int|null    $current_step_id
 * @property string      $status
 * @property int         $steps_completed
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon|null $next_step_at
 * @property array|null  $metadata
 */
class OmniSequenceRun extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_sequence_runs';

    protected $fillable = [
        'sequence_id',
        'company_id',
        'omni_customer_id',
        'current_step_id',
        'status',
        'steps_completed',
        'started_at',
        'completed_at',
        'next_step_at',
        'metadata',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
        'next_step_at'  => 'datetime',
        'metadata'      => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model) {
            if (empty($model->started_at)) {
                $model->started_at = now();
            }
        });
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(OmniSequence::class, 'sequence_id');
    }

    public function omniCustomer(): BelongsTo
    {
        return $this->belongsTo(OmniCustomer::class, 'omni_customer_id');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(OmniSequenceStep::class, 'current_step_id');
    }

    // ── Named scopes ─────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('next_step_at', '<=', now());
    }

    /**
     * Eager-load sequence + current step for processing jobs (N+1 guard).
     */
    public function scopeWithContext(Builder $query): Builder
    {
        return $query->with(['sequence', 'currentStep', 'omniCustomer']);
    }
}
