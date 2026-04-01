<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\Work\ActivityCompleted;
use App\Events\Work\ActivityCreated;
use App\Events\Work\ActivityDismissed;
use App\Events\Work\JobCompleted;
use App\Events\Work\JobStageChanged;
use App\Models\Crm\Customer;
use App\Models\Money\Invoice;
use App\Models\User;
use App\Models\Work\JobActivity;
use App\Models\Work\JobStage;
use App\Models\Work\JobTemplate;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Services\Work\JobBillingService;
use App\Services\Work\JobStageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Module 4 — fieldservice_activity integration tests.
 *
 * Covers:
 *  - JobActivity state machine (todo → done / cancel)
 *  - Required activity blocks job closure
 *  - Template activities auto-copied to new job
 *  - Customer unbilled jobs helper
 *  - ServiceJob helpers: canGenerateInvoice, hasRequiredActivitiesDone, revenueSummary
 *  - JobBillingService: revenueSummary, unbilledCompletedReport
 *  - Activity events dispatched correctly
 */
class Module4ActivityTest extends TestCase
{
    use RefreshDatabase;

    // ── Module 4 — Job Activity state machine ────────────────────────────────

    public function test_job_activity_defaults_to_todo_state(): void
    {
        $company = 50;

        $job = ServiceJob::factory()->create(['company_id' => $company]);

        $activity = JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'name'           => 'Check equipment',
        ]);

        $this->assertSame('todo', $activity->state);
        $this->assertFalse($activity->completed);
        $this->assertTrue($activity->isPending());
    }

    public function test_activity_complete_transitions_to_done_and_emits_event(): void
    {
        Event::fake();

        $company = 51;

        $user = User::factory()->create(['company_id' => $company]);
        $job  = ServiceJob::factory()->create(['company_id' => $company]);

        $activity = JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'name'           => 'Sign off',
        ]);

        $activity->complete($user);

        $activity->refresh();
        $this->assertSame('done', $activity->state);
        $this->assertTrue($activity->completed);
        $this->assertNotNull($activity->completed_on);
        $this->assertSame($user->id, $activity->completed_by);
        $this->assertFalse($activity->isPending());

        Event::assertDispatched(ActivityCompleted::class, function ($e) use ($activity, $user) {
            return $e->activity->id === $activity->id
                && $e->completedBy->id === $user->id;
        });
    }

    public function test_activity_dismiss_transitions_to_cancel_and_emits_event(): void
    {
        Event::fake();

        $company = 52;

        $job      = ServiceJob::factory()->create(['company_id' => $company]);
        $activity = JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
        ]);

        $activity->dismiss();

        $activity->refresh();
        $this->assertSame('cancel', $activity->state);
        $this->assertFalse($activity->isPending());

        Event::assertDispatched(ActivityDismissed::class, fn ($e) => $e->activity->id === $activity->id);
    }

    // ── Module 4 — Required activity blocks stage closure ────────────────────

    public function test_stage_transition_blocked_when_required_activity_pending(): void
    {
        $company = 53;

        $openStage = JobStage::factory()->create([
            'company_id' => $company,
            'is_default' => true,
            'is_closed'  => false,
            'sequence'   => 1,
        ]);

        $closedStage = JobStage::factory()->create([
            'company_id' => $company,
            'is_closed'  => true,
            'sequence'   => 2,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => Site::factory()->create(['company_id' => $company])->id,
            'stage_id'   => $openStage->id,
            'status'     => 'in_progress',
        ]);

        // Add a required pending activity
        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'required'       => true,
            'state'          => 'todo',
        ]);

        $this->expectException(ValidationException::class);

        app(JobStageService::class)->transition($job, $closedStage);
    }

    public function test_stage_transition_succeeds_when_required_activities_all_done(): void
    {
        Event::fake();

        $company = 54;

        $openStage = JobStage::factory()->create([
            'company_id' => $company,
            'is_default' => true,
            'is_closed'  => false,
            'sequence'   => 1,
        ]);

        $closedStage = JobStage::factory()->create([
            'company_id' => $company,
            'is_closed'  => true,
            'sequence'   => 2,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => Site::factory()->create(['company_id' => $company])->id,
            'stage_id'   => $openStage->id,
            'status'     => 'in_progress',
        ]);

        $user = User::factory()->create(['company_id' => $company]);

        // All required activities are done
        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'required'       => true,
            'state'          => 'done',
            'completed'      => true,
            'completed_by'   => $user->id,
            'completed_on'   => now(),
        ]);

        $result = app(JobStageService::class)->transition($job, $closedStage);

        $this->assertSame('completed', $result->status);
        Event::assertDispatched(JobCompleted::class);
    }

    public function test_non_required_pending_activity_does_not_block_closure(): void
    {
        Event::fake();

        $company = 55;

        $openStage = JobStage::factory()->create([
            'company_id' => $company,
            'is_default' => true,
            'is_closed'  => false,
            'sequence'   => 1,
        ]);

        $closedStage = JobStage::factory()->create([
            'company_id' => $company,
            'is_closed'  => true,
            'sequence'   => 2,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => Site::factory()->create(['company_id' => $company])->id,
            'stage_id'   => $openStage->id,
        ]);

        // Non-required, still pending
        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'required'       => false,
            'state'          => 'todo',
        ]);

        $result = app(JobStageService::class)->transition($job, $closedStage);

        $this->assertSame('completed', $result->status);
    }

    // ── Module 4 — Template activity auto-copy ───────────────────────────────

    public function test_template_activities_are_copied_to_job_via_copy_method(): void
    {
        $company = 56;

        $template = JobTemplate::factory()->create(['company_id' => $company]);

        JobActivity::factory()->count(2)->create([
            'company_id'  => $company,
            'template_id' => $template->id,
            'required'    => true,
        ]);

        $job = $template->instantiateJob(['company_id' => $company]);
        $job->save();

        $template->copyActivitiesTo($job);

        $this->assertSame(2, $job->activities()->count());
        $this->assertTrue($job->activities()->where('required', true)->exists());
    }

    public function test_copied_activities_start_in_todo_state(): void
    {
        $company = 57;

        $template = JobTemplate::factory()->create(['company_id' => $company]);

        JobActivity::factory()->create([
            'company_id'  => $company,
            'template_id' => $template->id,
            'name'        => 'Inspect HVAC',
            'required'    => true,
        ]);

        $job = $template->instantiateJob(['company_id' => $company]);
        $job->save();

        $template->copyActivitiesTo($job);

        $copied = $job->activities()->first();
        $this->assertSame('todo', $copied->state);
        $this->assertFalse($copied->completed);
        $this->assertSame('Inspect HVAC', $copied->name);
    }

    // ── Module 4 — ServiceJob helpers ────────────────────────────────────────

    public function test_has_required_activities_done_returns_true_when_no_activities(): void
    {
        $company = 58;

        $job = ServiceJob::factory()->create(['company_id' => $company]);

        $this->assertTrue($job->hasRequiredActivitiesDone());
    }

    public function test_has_required_activities_done_returns_false_when_required_pending(): void
    {
        $company = 59;

        $job = ServiceJob::factory()->create(['company_id' => $company]);

        JobActivity::factory()->create([
            'company_id'     => $company,
            'service_job_id' => $job->id,
            'required'       => true,
            'state'          => 'todo',
        ]);

        $this->assertFalse($job->hasRequiredActivitiesDone());
    }

    public function test_can_generate_invoice_returns_true_for_eligible_job(): void
    {
        $company = 60;

        $job = ServiceJob::factory()->create([
            'company_id'   => $company,
            'is_billable'  => true,
            'invoice_id'   => null,
            'status'       => 'completed',
            'billable_rate' => 50.00,
        ]);

        $this->assertTrue($job->canGenerateInvoice());
    }

    public function test_can_generate_invoice_returns_false_when_already_invoiced(): void
    {
        $company = 61;

        $customer = Customer::factory()->create(['company_id' => $company]);
        $invoice  = Invoice::factory()->create([
            'company_id'  => $company,
            'customer_id' => $customer->id,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id'  => $company,
            'is_billable' => true,
            'invoice_id'  => $invoice->id,
            'status'      => 'completed',
        ]);

        $this->assertFalse($job->canGenerateInvoice());
    }

    public function test_revenue_summary_returns_expected_keys(): void
    {
        $company = 62;

        $job = ServiceJob::factory()->create([
            'company_id'   => $company,
            'is_billable'  => true,
            'billable_rate' => 60.00,
            'status'       => 'completed',
        ]);

        $summary = $job->revenueSummary();

        $this->assertArrayHasKey('duration_hours', $summary);
        $this->assertArrayHasKey('billable_rate', $summary);
        $this->assertArrayHasKey('estimated_revenue', $summary);
        $this->assertArrayHasKey('invoiced_total', $summary);
        $this->assertArrayHasKey('invoice_status', $summary);
        $this->assertSame(60.0, $summary['billable_rate']);
        $this->assertNull($summary['invoiced_total']);
    }

    // ── Module 3 completion — Customer unbilled jobs helper ──────────────────

    public function test_customer_unbilled_jobs_returns_only_unbilled_completed(): void
    {
        $company  = 63;
        $customer = Customer::factory()->create(['company_id' => $company]);

        // Unbilled completed
        $unbilledJob = ServiceJob::factory()->create([
            'company_id'  => $company,
            'customer_id' => $customer->id,
            'is_billable' => true,
            'invoice_id'  => null,
            'status'      => 'completed',
        ]);

        // Billed completed (excluded)
        $billedInvoice = Invoice::factory()->create(['company_id' => $company, 'customer_id' => $customer->id]);
        ServiceJob::factory()->create([
            'company_id'  => $company,
            'customer_id' => $customer->id,
            'is_billable' => true,
            'invoice_id'  => $billedInvoice->id,
            'status'      => 'completed',
        ]);

        // Non-billable completed (excluded)
        ServiceJob::factory()->create([
            'company_id'  => $company,
            'customer_id' => $customer->id,
            'is_billable' => false,
            'status'      => 'completed',
        ]);

        $unbilled = $customer->unbilledJobs();

        $this->assertCount(1, $unbilled);
        $this->assertSame($unbilledJob->id, $unbilled->first()->id);
    }

    // ── Module 3 completion — JobBillingService reporting helpers ────────────

    public function test_revenue_summary_aggregates_are_correct(): void
    {
        $company  = 64;
        $customer = Customer::factory()->create(['company_id' => $company]);
        $invoice  = Invoice::factory()->create([
            'company_id'  => $company,
            'customer_id' => $customer->id,
            'total'       => 200.00,
        ]);

        // Billed job
        ServiceJob::factory()->create([
            'company_id'  => $company,
            'customer_id' => $customer->id,
            'is_billable' => true,
            'invoice_id'  => $invoice->id,
            'status'      => 'completed',
        ]);

        // Unbilled completed job
        ServiceJob::factory()->create([
            'company_id'    => $company,
            'customer_id'   => $customer->id,
            'is_billable'   => true,
            'invoice_id'    => null,
            'status'        => 'completed',
            'billable_rate' => 50.00,
        ]);

        $summary = app(JobBillingService::class)->revenueSummary($company);

        $this->assertSame($company, $summary['company_id']);
        $this->assertSame(2, $summary['billable_jobs']);
        $this->assertSame(1, $summary['unbilled_completed']);
        $this->assertSame(1, $summary['invoiced_jobs']);
        $this->assertArrayHasKey('estimated_unbilled_revenue', $summary);
        $this->assertArrayHasKey('invoiced_revenue', $summary);
    }

    public function test_unbilled_completed_report_returns_correct_structure(): void
    {
        $company  = 65;
        $customer = Customer::factory()->create(['company_id' => $company]);

        ServiceJob::factory()->create([
            'company_id'    => $company,
            'customer_id'   => $customer->id,
            'is_billable'   => true,
            'invoice_id'    => null,
            'status'        => 'completed',
            'billable_rate' => 75.00,
            'date_start'    => now()->subHours(3),
            'date_end'      => now()->subHours(1),
        ]);

        $report = app(JobBillingService::class)->unbilledCompletedReport($company);

        $this->assertSame($company, $report['company_id']);
        $this->assertSame(1, $report['count']);
        $this->assertCount(1, $report['jobs']);
        $this->assertArrayHasKey('estimated_revenue', $report['jobs'][0]);
        $this->assertArrayHasKey('completed_at', $report['jobs'][0]);
    }
}
