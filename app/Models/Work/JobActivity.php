<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Events\Work\ActivityCreated;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Team\Team;
use App\Models\User;
use Carbon\Carbon;
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
 * @property int              $id
 * @property int              $company_id
 * @property int|null         $service_job_id
 * @property int|null         $template_id
 * @property string           $name
 * @property string|null      $ref
 * @property int              $sequence
 * @property bool             $required
 * @property bool             $completed
 * @property string           $state           todo | done | cancel
 * @property int|null         $completed_by
 * @property Carbon|null      $completed_on
 * @property int|null         $assigned_to
 * @property int|null         $team_id
 * @property Carbon|null      $follow_up_at
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
        'assigned_to',
        'team_id',
        'follow_up_at',
    ];

    protected $casts = [
        'required'     => 'boolean',
        'completed'    => 'boolean',
        'sequence'     => 'integer',
        'completed_on' => 'datetime',
        'follow_up_at' => 'datetime',
    ];

    protected $attributes = [
        'required'  => false,
        'completed' => false,
        'state'     => 'todo',
        'sequence'  => 0,
    ];

    // ── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::created(static function (self $activity): void {
            ActivityCreated::dispatch($activity);
        });
    }

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

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

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

    /**
     * Schedule a follow-up date/time for this activity.
     *
     * Useful for billing follow-ups and activity reminders. Dispatches the
     * ActivityFollowUpScheduled event so automation can react.
     */
    public function scheduleFollowUp(Carbon $followUpAt): void
    {
        $this->update(['follow_up_at' => $followUpAt]);

        \App\Events\Work\ActivityFollowUpScheduled::dispatch($this);
    }

    /**
     * Assign this activity to a user.
     */
    public function assignTo(User $user): void
    {
        $this->update(['assigned_to' => $user->id]);
    }

    /**
     * Assign this activity to a team.
     */
    public function assignToTeam(Team $team): void
    {
        $this->update(['team_id' => $team->id]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Whether this activity is still pending (not done, not cancelled).
     */
    public function isPending(): bool
    {
        return $this->state === 'todo';
    }

    /**
     * Whether a follow-up is due (follow_up_at is in the past and activity is still pending).
     */
    public function isFollowUpDue(): bool
    {
        return $this->isPending()
            && $this->follow_up_at !== null
            && $this->follow_up_at->isPast();
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

    /**
     * Scope: activities assigned to a specific user.
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope: activities assigned to a specific team.
     */
    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope: activities with a follow-up due on or before the given date.
     */
    public function scopeDueBy(Builder $query, Carbon $date): Builder
    {
        return $query->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<=', $date);
    }

    /**
     * Scope: pending activities whose follow-up date is in the past.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('state', 'todo')
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<', now());
    }

    /**
     * Scope: activities linked to jobs for a specific customer.
     */
    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->whereHas('job', fn (Builder $q) => $q->where('customer_id', $customerId));
    }

    /**
     * Scope: activities linked to jobs at a specific site.
     */
    public function scopeForSite(Builder $query, int $siteId): Builder
    {
        return $query->whereHas('job', fn (Builder $q) => $q->where('site_id', $siteId));
    }

    /**
     * Scope: activities ordered for timeline display (sequence ASC, then created_at ASC).
     */
    public function scopeTimeline(Builder $query): Builder
    {
        return $query->orderBy('sequence')->orderBy('created_at');
    }

    /**
     * Scope: activities with a billing follow-up date set and still pending.
     *
     * These represent activities that need billing attention.
     */
    public function scopeBillingFollowUp(Builder $query): Builder
    {
        return $query->whereNotNull('follow_up_at')
            ->where('state', 'todo');
    }
}
