<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\Work\ActivityCreated;
use App\Events\Work\ActivityFollowUpScheduled;
use App\Models\Crm\Customer;
use App\Models\User;
use App\Models\Work\JobActivity;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Services\Work\JobActivityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Module 4 — fieldservice_activity lifecycle completion tests.
 *
 * Covers new features added to complete the Module 4 merge:
 *  - ActivityCreated auto-dispatched on create
 *  - follow_up_at scheduling + ActivityFollowUpScheduled event
 *  - assigned_to / team_id assignment helpers
 *  - scopeAssignedTo, scopeForTeam, scopeOverdue, scopeDueBy
 *  - scopeForCustomer, scopeForSite
 *  - scopeTimeline ordering
 *  - scopeBillingFollowUp
 *  - Customer::pendingActivities()
 *  - Site::pendingActivities()
 *  - ServiceJob::activityTimeline()
 *  - JobActivityService: stateSummary, pendingBillingFollowUps, overdueFollowUps
 *  - JobActivityService: reorder bulk sequence update
 */
class Module4ActivityLifecycleTest extends TestCase
{
    use RefreshDatabase;

    // ── Boot observer ────────────────────────────────────────────────────────

    public function test_activity_created_event_dispatched_on_model_create(): void
    {
        Event::fake();

        $job = ServiceJob::factory()->create(['company_id' => 80]);

        JobActivity::factory()->create([
            'company_id'     => 80,
            'service_job_id' => $job->id,
            'name'           => 'Boot test',
        ]);

        Event::assertDispatched(ActivityCreated::class);
    }

    // ── Follow-up scheduling ─────────────────────────────────────────────────

    public function test_schedule_follow_up_sets_date_and_dispatches_event(): void
    {
        Event::fake();

        $job      = ServiceJob::factory()->create(['company_id' => 81]);
        $activity = JobActivity::factory()->create([
            'company_id'     => 81,
            'service_job_id' => $job->id,
        ]);

        $followUp = Carbon::parse('2025-06-01 10:00:00');
        $activity->scheduleFollowUp($followUp);

        $activity->refresh();
        $this->assertTrue($activity->follow_up_at->equalTo($followUp));

        Event::assertDispatched(ActivityFollowUpScheduled::class, fn ($e) => $e->activity->id === $activity->id);
    }

    public function test_is_follow_up_due_returns_true_for_overdue_pending_activity(): void
    {
        $job      = ServiceJob::factory()->create(['company_id' => 82]);
        $activity = JobActivity::factory()->create([
            'company_id'     => 82,
            'service_job_id' => $job->id,
            'state'          => 'todo',
            'follow_up_at'   => Carbon::yesterday(),
        ]);

        $this->assertTrue($activity->isFollowUpDue());
    }

    public function test_is_follow_up_due_returns_false_for_future_date(): void
    {
        $job      = ServiceJob::factory()->create(['company_id' => 83]);
        $activity = JobActivity::factory()->create([
            'company_id'     => 83,
            'service_job_id' => $job->id,
            'state'          => 'todo',
            'follow_up_at'   => Carbon::tomorrow(),
        ]);

        $this->assertFalse($activity->isFollowUpDue());
    }

    public function test_is_follow_up_due_returns_false_for_completed_activity(): void
    {
        $job      = ServiceJob::factory()->create(['company_id' => 84]);
        $activity = JobActivity::factory()->create([
            'company_id'     => 84,
            'service_job_id' => $job->id,
            'state'          => 'done',
            'follow_up_at'   => Carbon::yesterday(),
        ]);

        $this->assertFalse($activity->isFollowUpDue());
    }

    // ── Assignment ───────────────────────────────────────────────────────────

    public function test_assign_to_user_sets_assigned_to(): void
    {
        $company  = 85;
        $user     = User::factory()->create(['company_id' => $company]);
        $job      = ServiceJob::factory()->create(['company_id' => $company]);
        $activity = JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
        ]);

        $activity->assignTo($user);
        $activity->refresh();

        $this->assertSame($user->id, $activity->assigned_to);
        $this->assertSame($user->id, $activity->assignedUser->id);
    }

    public function test_assign_to_team_sets_team_id(): void
    {
        $company  = 86;
        $team     = \App\Models\Team\Team::create([
            'company_id' => $company,
            'user_id'    => null,
            'name'       => 'Test Team',
        ]);
        $job      = ServiceJob::factory()->create(['company_id' => $company]);
        $activity = JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
        ]);

        $activity->assignToTeam($team);
        $activity->refresh();

        $this->assertSame($team->id, $activity->team_id);
        $this->assertSame($team->id, $activity->team->id);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function test_scope_assigned_to_filters_by_user(): void
    {
        $company   = 87;
        $user      = User::factory()->create(['company_id' => $company]);
        $job       = ServiceJob::factory()->create(['company_id' => $company]);

        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'assigned_to' => $user->id]);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'assigned_to' => null]);

        $results = JobActivity::assignedTo($user->id)->where('company_id', $company)->get();

        $this->assertCount(1, $results);
        $this->assertSame($user->id, $results->first()->assigned_to);
    }

    public function test_scope_for_team_filters_by_team(): void
    {
        $company = 88;
        $team    = \App\Models\Team\Team::create([
            'company_id' => $company,
            'user_id'    => null,
            'name'       => 'Team 88',
        ]);
        $job     = ServiceJob::factory()->create(['company_id' => $company]);

        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'team_id' => $team->id]);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'team_id' => null]);

        $results = JobActivity::forTeam($team->id)->where('company_id', $company)->get();

        $this->assertCount(1, $results);
        $this->assertSame($team->id, $results->first()->team_id);
    }

    public function test_scope_overdue_returns_pending_with_past_follow_up(): void
    {
        $company = 89;
        $job     = ServiceJob::factory()->create(['company_id' => $company]);

        // Overdue
        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'state'          => 'todo',
            'follow_up_at'   => Carbon::yesterday(),
        ]);

        // Future follow-up — not overdue
        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'state'          => 'todo',
            'follow_up_at'   => Carbon::tomorrow(),
        ]);

        // Done with past date — not overdue
        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'state'          => 'done',
            'follow_up_at'   => Carbon::yesterday(),
        ]);

        $results = JobActivity::overdue()->where('company_id', $company)->get();

        $this->assertCount(1, $results);
    }

    public function test_scope_for_customer_filters_by_customer(): void
    {
        $company  = 90;
        $customer = Customer::factory()->create(['company_id' => $company]);
        $job      = ServiceJob::factory()->create(['company_id' => $company, 'customer_id' => $customer->id]);
        $otherJob = ServiceJob::factory()->create(['company_id' => $company]);

        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id]);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $otherJob->id]);

        $results = JobActivity::forCustomer($customer->id)->where('company_id', $company)->get();

        $this->assertCount(1, $results);
    }

    public function test_scope_for_site_filters_by_site(): void
    {
        $company = 91;
        $site    = Site::factory()->create(['company_id' => $company]);
        $job     = ServiceJob::factory()->create(['company_id' => $company, 'site_id' => $site->id]);
        $otherJob = ServiceJob::factory()->create(['company_id' => $company]);

        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id]);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $otherJob->id]);

        $results = JobActivity::forSite($site->id)->where('company_id', $company)->get();

        $this->assertCount(1, $results);
    }

    public function test_scope_billing_follow_up_returns_pending_with_follow_up_set(): void
    {
        $company = 92;
        $job     = ServiceJob::factory()->create(['company_id' => $company]);

        // Should be included
        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'state'          => 'todo',
            'follow_up_at'   => Carbon::tomorrow(),
        ]);

        // No follow_up_at — excluded
        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'state'          => 'todo',
            'follow_up_at'   => null,
        ]);

        // Done — excluded
        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'state'          => 'done',
            'follow_up_at'   => Carbon::tomorrow(),
        ]);

        $results = JobActivity::billingFollowUp()->where('company_id', $company)->get();

        $this->assertCount(1, $results);
    }

    // ── Timeline and helpers ─────────────────────────────────────────────────

    public function test_service_job_activity_timeline_returns_ordered_results(): void
    {
        $company = 93;
        $job     = ServiceJob::factory()->create(['company_id' => $company]);

        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'sequence' => 30, 'name' => 'C']);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'sequence' => 10, 'name' => 'A']);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'sequence' => 20, 'name' => 'B']);

        $timeline = $job->activityTimeline();

        $this->assertCount(3, $timeline);
        $this->assertSame('A', $timeline->first()->name);
        $this->assertSame('C', $timeline->last()->name);
    }

    // ── Customer and Site helpers ────────────────────────────────────────────

    public function test_customer_pending_activities_returns_only_todo_for_customer(): void
    {
        $company  = 94;
        $customer = Customer::factory()->create(['company_id' => $company]);
        $job      = ServiceJob::factory()->create(['company_id' => $company, 'customer_id' => $customer->id]);

        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'state' => 'todo']);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'state' => 'done']);

        // Activity on a different customer's job — should not appear
        $otherJob = ServiceJob::factory()->create(['company_id' => $company]);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $otherJob->id, 'state' => 'todo']);

        $pending = $customer->pendingActivities();

        $this->assertCount(1, $pending);
        $this->assertSame('todo', $pending->first()->state);
    }

    public function test_site_pending_activities_returns_only_todo_for_site(): void
    {
        $company = 95;
        $site    = Site::factory()->create(['company_id' => $company]);
        $job     = ServiceJob::factory()->create(['company_id' => $company, 'site_id' => $site->id]);

        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'state' => 'todo']);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'state' => 'cancel']);

        // Activity on a different site — should not appear
        $otherJob = ServiceJob::factory()->create(['company_id' => $company]);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $otherJob->id, 'state' => 'todo']);

        $pending = $site->pendingActivities();

        $this->assertCount(1, $pending);
    }

    // ── JobActivityService ───────────────────────────────────────────────────

    public function test_job_activity_service_state_summary(): void
    {
        $company = 96;
        $job     = ServiceJob::factory()->create(['company_id' => $company]);

        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'state' => 'todo', 'required' => true]);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'state' => 'todo', 'required' => false]);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'state' => 'done']);
        JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'state' => 'cancel']);

        $summary = app(JobActivityService::class)->stateSummary($job);

        $this->assertSame(4, $summary['total']);
        $this->assertSame(2, $summary['pending']);
        $this->assertSame(1, $summary['done']);
        $this->assertSame(1, $summary['cancelled']);
        $this->assertSame(1, $summary['required_pending']);
    }

    public function test_job_activity_service_pending_billing_follow_ups(): void
    {
        $company = 97;
        $job     = ServiceJob::factory()->create(['company_id' => $company]);

        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'state'          => 'todo',
            'follow_up_at'   => Carbon::tomorrow(),
        ]);

        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'state'          => 'todo',
            'follow_up_at'   => null,
        ]);

        $result = app(JobActivityService::class)->pendingBillingFollowUps($company);

        $this->assertCount(1, $result);
    }

    public function test_job_activity_service_reorder_updates_sequences(): void
    {
        $company = 98;
        $job     = ServiceJob::factory()->create(['company_id' => $company]);

        $a1 = JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'sequence' => 0]);
        $a2 = JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'sequence' => 1]);
        $a3 = JobActivity::factory()->create(['company_id' => $company, 'service_job_id' => $job->id, 'sequence' => 2]);

        // Reverse order
        app(JobActivityService::class)->reorder($job, [$a3->id, $a2->id, $a1->id]);

        $this->assertSame(0, $a3->fresh()->sequence);
        $this->assertSame(1, $a2->fresh()->sequence);
        $this->assertSame(2, $a1->fresh()->sequence);
    }
}
