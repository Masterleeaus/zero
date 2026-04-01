<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * JobActivity
 *
 * Tracks individual activities (action items) on a service job or a
 * job template.  Ported from Odoo fieldservice_activity (fsm.activity).
 *
 * State machine:  todo → done  (via complete())
 *                       → cancel (via dismiss())
 *
 * When `required` is true, the job cannot be moved to a closed stage until
 * this activity reaches "done".
 *
 * @property int         $id
 * @property int         $company_id
 * @property int|null    $service_job_id
 * @property int|null    $template_id
 * @property string      $name
 * @property string|null $ref
 * @property int         $sequence
 * @property bool        $required
 * @property bool        $completed
 * @property string      $state           todo | done | cancel
 * @property int|null    $completed_by
 * @property \Carbon\Carbon|null $completed_on
 */
class JobActivity extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'service_job_id',
        'template_id',
        'name',
        'ref',
        'sequence',
        'required',
        'completed',
        'state',
        'completed_by',
        'completed_on',
    ];

    protected $casts = [
        'required'     => 'boolean',
        'completed'    => 'boolean',
        'sequence'     => 'integer',
        'completed_on' => 'datetime',
    ];

    protected $attributes = [
        'required'  => false,
        'completed' => false,
        'state'     => 'todo',
        'sequence'  => 0,
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function job(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(JobTemplate::class, 'template_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // ── State machine ────────────────────────────────────────────────────────

    /**
     * Mark this activity as completed.
     *
     * Records the completing user and timestamp, transitions state to "done",
     * then dispatches the ActivityCompleted event.
     */
    public function complete(User $user): void
    {
        $this->update([
            'state'        => 'done',
            'completed'    => true,
            'completed_by' => $user->id,
            'completed_on' => now(),
        ]);

        \App\Events\Work\ActivityCompleted::dispatch($this, $user);
    }

    /**
     * Dismiss (cancel) this activity.
     *
     * Dispatches the ActivityDismissed event.
     */
    public function dismiss(): void
    {
        $this->update(['state' => 'cancel']);

        \App\Events\Work\ActivityDismissed::dispatch($this);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Whether this activity is still pending (not done, not cancelled).
     */
    public function isPending(): bool
    {
        return $this->state === 'todo';
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('state', 'todo');
    }

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('required', true);
    }

    public function scopeForJob(Builder $query, int $jobId): Builder
    {
        return $query->where('service_job_id', $jobId);
    }

    public function scopeForTemplate(Builder $query, int $templateId): Builder
    {
        return $query->where('template_id', $templateId);
    }
}
