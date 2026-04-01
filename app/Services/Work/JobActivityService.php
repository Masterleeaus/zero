<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\ActivityCreated;
use App\Models\Team\Team;
use App\Models\User;
use App\Models\Work\JobActivity;
use App\Models\Work\JobTemplate;
use App\Models\Work\ServiceJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * JobActivityService
 *
 * Central service for all activity lifecycle operations.
 *
 * Responsibilities:
 *  - Create activities (ad-hoc for a job, or template definitions)
 *  - Complete and dismiss activities with signal emission
 *  - Schedule follow-up dates for billing reminders and activity tracking
 *  - Assign activities to users and teams
 *  - Bulk reorder activities on a job by sequence
 *  - Timeline query helpers for jobs, customers, and sites
 *  - Billing follow-up report
 */
class JobActivityService
{
    // ── Create ───────────────────────────────────────────────────────────────

    /**
     * Create an ad-hoc activity for a live service job.
     *
     * The ActivityCreated event is dispatched via the model's boot observer.
     *
     * @param  array<string, mixed>  $data
     */
    public function createForJob(ServiceJob $job, array $data): JobActivity
    {
        return JobActivity::create(array_merge([
            'company_id'     => $job->company_id,
            'service_job_id' => $job->id,
            'state'          => 'todo',
            'completed'      => false,
        ], $data));
    }

    /**
     * Add an activity definition to a job template.
     *
     * Template activities are not live activities; they serve as blueprints
     * that are copied onto jobs when the template is instantiated.
     *
     * @param  array<string, mixed>  $data
     */
    public function createForTemplate(JobTemplate $template, array $data): JobActivity
    {
        return JobActivity::create(array_merge([
            'company_id'  => $template->company_id,
            'template_id' => $template->id,
            'state'       => 'todo',
            'completed'   => false,
        ], $data));
    }

    // ── State transitions ────────────────────────────────────────────────────

    /**
     * Complete an activity.
     *
     * Wraps the model's complete() method and returns the refreshed activity.
     */
    public function complete(JobActivity $activity, User $user): JobActivity
    {
        $activity->complete($user);

        return $activity->fresh();
    }

    /**
     * Dismiss (cancel) an activity.
     *
     * Wraps the model's dismiss() method and returns the refreshed activity.
     */
    public function dismiss(JobActivity $activity): JobActivity
    {
        $activity->dismiss();

        return $activity->fresh();
    }

    // ── Follow-up scheduling ─────────────────────────────────────────────────

    /**
     * Schedule a follow-up date/time for an activity.
     *
     * Used to track billing follow-ups, customer callbacks, and other
     * time-sensitive action items.
     */
    public function scheduleFollowUp(JobActivity $activity, Carbon $followUpAt): JobActivity
    {
        $activity->scheduleFollowUp($followUpAt);

        return $activity->fresh();
    }

    // ── Assignment ───────────────────────────────────────────────────────────

    /**
     * Assign an activity to a specific user.
     */
    public function assignTo(JobActivity $activity, User $user): JobActivity
    {
        $activity->assignTo($user);

        return $activity->fresh();
    }

    /**
     * Assign an activity to a team.
     */
    public function assignToTeam(JobActivity $activity, Team $team): JobActivity
    {
        $activity->assignToTeam($team);

        return $activity->fresh();
    }

    // ── Ordering ─────────────────────────────────────────────────────────────

    /**
     * Bulk reorder activities on a job.
     *
     * Accepts an array of activity IDs in the desired order. Each activity's
     * sequence is set to its array position (0-indexed). Only activities
     * belonging to the given job are updated; any foreign IDs are silently
     * ignored.
     *
     * Uses a single CASE … WHEN batch query to minimise round-trips.
     *
     * @param  array<int>  $orderedIds  Activity IDs in desired sequence order
     */
    public function reorder(ServiceJob $job, array $orderedIds): void
    {
        if (empty($orderedIds)) {
            return;
        }

        DB::transaction(function () use ($job, $orderedIds): void {
            $cases   = [];
            $bindings = [];

            foreach ($orderedIds as $position => $activityId) {
                $cases[]    = 'WHEN id = ? THEN ?';
                $bindings[] = $activityId;
                $bindings[] = $position;
            }

            $bindings[] = $job->id;
            $inList = implode(',', array_fill(0, count($orderedIds), '?'));
            $bindings = array_merge($bindings, $orderedIds);

            $caseExpr = implode(' ', $cases);

            DB::statement(
                "UPDATE job_activities SET sequence = CASE {$caseExpr} ELSE sequence END WHERE service_job_id = ? AND id IN ({$inList})",
                $bindings
            );
        });
    }

    // ── Query helpers ────────────────────────────────────────────────────────

    /**
     * Return the ordered activity timeline for a job.
     *
     * Activities are ordered by sequence ASC, then created_at ASC.
     * Relations (completedByUser, assignedUser, team) are eager-loaded.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, JobActivity>
     */
    public function timelineForJob(ServiceJob $job): \Illuminate\Database\Eloquent\Collection
    {
        return $job->activityTimeline();
    }

    /**
     * Count the number of pending required activities on a job.
     *
     * Used by the stage-transition guard to decide whether closure is allowed.
     */
    public function pendingRequiredCount(ServiceJob $job): int
    {
        return $job->activities()
            ->where('required', true)
            ->where('state', 'todo')
            ->count();
    }

    /**
     * Return all pending activities with a billing follow-up date set,
     * scoped to the given company.
     *
     * Ordered by follow_up_at ASC so the most overdue items appear first.
     * Useful for billing dashboards and automated reminders.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, JobActivity>
     */
    public function pendingBillingFollowUps(int $companyId): \Illuminate\Database\Eloquent\Collection
    {
        return JobActivity::query()
            ->where('company_id', $companyId)
            ->billingFollowUp()
            ->orderBy('follow_up_at')
            ->with(['job', 'assignedUser', 'team'])
            ->get();
    }

    /**
     * Return activities overdue for follow-up, scoped to a company.
     *
     * An activity is overdue when it is still pending (todo) and its
     * follow_up_at date is in the past.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, JobActivity>
     */
    public function overdueFollowUps(int $companyId): \Illuminate\Database\Eloquent\Collection
    {
        return JobActivity::query()
            ->where('company_id', $companyId)
            ->overdue()
            ->orderBy('follow_up_at')
            ->with(['job', 'assignedUser', 'team'])
            ->get();
    }

    /**
     * Return a summary of activity states for a given job.
     *
     * @return array{total: int, pending: int, done: int, cancelled: int, required_pending: int}
     */
    public function stateSummary(ServiceJob $job): array
    {
        $activities = $job->activities()->get(['state', 'required']);

        return [
            'total'            => $activities->count(),
            'pending'          => $activities->where('state', 'todo')->count(),
            'done'             => $activities->where('state', 'done')->count(),
            'cancelled'        => $activities->where('state', 'cancel')->count(),
            'required_pending' => $activities->where('state', 'todo')->where('required', true)->count(),
        ];
    }
}
