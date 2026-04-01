<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\Work\AgreementServiceConsumed;
use App\Events\Work\JobCompletedBillable;
use App\Events\Work\JobCompleted;
use App\Events\Work\JobMarkedBillable;
use App\Events\Work\JobReadyForInvoice;
use App\Events\Work\JobStageChanged;
use App\Events\Work\ServiceInvoiceGenerated;
use App\Models\Crm\Customer;
use App\Models\Money\Invoice;
use App\Models\User;
use App\Models\Work\JobStage;
use App\Models\Work\JobTemplate;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Services\Work\JobBillingService;
use App\Services\Work\JobStageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Module2Module3WorkflowTest extends TestCase
{
    use RefreshDatabase;

    // ── Stage A: Module 2 FSM lifecycle ─────────────────────────────────────

    public function test_job_stage_service_transitions_job_to_new_stage(): void
    {
        Event::fake();

        $company = 20;

        $from = JobStage::factory()->create([
            'company_id' => $company,
            'stage_type' => 'order',
            'is_default' => true,
            'sequence'   => 1,
        ]);

        $to = JobStage::factory()->create([
            'company_id'     => $company,
            'stage_type'     => 'order',
            'is_default'     => false,
            'is_invoiceable' => false,
            'is_closed'      => false,
            'sequence'       => 2,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => Site::factory()->create(['company_id' => $company])->id,
            'stage_id'   => $from->id,
            'status'     => 'scheduled',
        ]);

        $service = new JobStageService();
        $updated = $service->transition($job, $to);

        $this->assertEquals($to->id, $updated->stage_id);
        $this->assertEquals('in_progress', $updated->status);

        Event::assertDispatched(JobStageChanged::class, fn ($e) => $e->job->id === $job->id
            && $e->newStage->id === $to->id);
    }

    public function test_stage_transition_blocked_when_signature_required_but_missing(): void
    {
        $company = 21;

        $from = JobStage::factory()->create(['company_id' => $company, 'stage_type' => 'order']);
        $to   = JobStage::factory()->create([
            'company_id'       => $company,
            'stage_type'       => 'order',
            'require_signature'=> true,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => Site::factory()->create(['company_id' => $company])->id,
            'stage_id'   => $from->id,
            'signed_on'  => null,
        ]);

        $this->expectException(ValidationException::class);

        (new JobStageService())->transition($job, $to);
    }

    public function test_stage_transition_succeeds_when_signature_present(): void
    {
        Event::fake();

        $company = 22;

        $from = JobStage::factory()->create(['company_id' => $company, 'stage_type' => 'order']);
        $to   = JobStage::factory()->create([
            'company_id'        => $company,
            'stage_type'        => 'order',
            'require_signature' => true,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => Site::factory()->create(['company_id' => $company])->id,
            'stage_id'   => $from->id,
            'signed_on'  => now(),
            'signed_by'  => 'John Doe',
        ]);

        $updated = (new JobStageService())->transition($job, $to);

        $this->assertEquals($to->id, $updated->stage_id);
    }

    public function test_closed_stage_emits_job_completed_event(): void
    {
        Event::fake();

        $company = 23;

        $from = JobStage::factory()->create(['company_id' => $company, 'stage_type' => 'order']);
        $closedStage = JobStage::factory()->create([
            'company_id' => $company,
            'stage_type' => 'order',
            'is_closed'  => true,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => Site::factory()->create(['company_id' => $company])->id,
            'stage_id'   => $from->id,
        ]);

        (new JobStageService())->transition($job, $closedStage);

        Event::assertDispatched(JobCompleted::class, fn ($e) => $e->job->id === $job->id);
    }

    public function test_job_template_instantiates_service_job_with_defaults(): void
    {
        $template = JobTemplate::factory()->create([
            'company_id' => 30,
            'name'       => 'Quarterly Deep Clean',
            'duration'   => 3.0,
            'instructions' => 'Deep clean everything',
        ]);

        $job = $template->instantiateJob(['site_id' => 99]);

        $this->assertEquals($template->id, $job->template_id);
        $this->assertEquals('Quarterly Deep Clean', $job->title);
        $this->assertEquals(3.0, $job->scheduled_duration);
        $this->assertEquals(99, $job->site_id);
        $this->assertFalse($job->exists);
    }

    public function test_site_inherits_workers_to_job(): void
    {
        $company = 31;

        $user1 = User::factory()->create(['company_id' => $company]);
        $user2 = User::factory()->create(['company_id' => $company]);

        $site = Site::factory()->create(['company_id' => $company]);
        $site->workers()->attach([$user1->id, $user2->id]);

        $job = ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => $site->id,
        ]);

        $site->inheritWorkersToJob($job);

        $workerIds = $job->workers()->pluck('users.id')->sort()->values()->all();
        $this->assertCount(2, $workerIds);
        $this->assertContains($user1->id, $workerIds);
        $this->assertContains($user2->id, $workerIds);
    }

    public function test_service_job_dispatch_ready_scope(): void
    {
        $company = 32;

        $site = Site::factory()->create(['company_id' => $company]);
        $user = User::factory()->create(['company_id' => $company]);

        // Ready: has site, assigned user, and scheduled start
        ServiceJob::factory()->create([
            'company_id'         => $company,
            'site_id'            => $site->id,
            'assigned_user_id'   => $user->id,
            'scheduled_date_start' => now()->addHour(),
            'status'             => 'scheduled',
        ]);

        // Not ready: missing assigned user
        ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => $site->id,
            'status'     => 'scheduled',
        ]);

        $this->actingAs(User::factory()->create(['company_id' => $company]));

        $this->assertSame(1, ServiceJob::dispatchReady()->count());
    }

    public function test_priority_normalizer(): void
    {
        $this->assertEquals('high', ServiceJob::normalizePriority('high'));
        $this->assertEquals('low', ServiceJob::normalizePriority('low'));
        $this->assertEquals('normal', ServiceJob::normalizePriority('normal'));
        $this->assertEquals('normal', ServiceJob::normalizePriority('unknown'));
        $this->assertEquals('normal', ServiceJob::normalizePriority(''));
    }

    // ── Stage B: Module 3 Billing ────────────────────────────────────────────

    public function test_job_billing_service_marks_job_billable_and_emits_event(): void
    {
        Event::fake();

        $company = 40;

        $job = ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => Site::factory()->create(['company_id' => $company])->id,
            'is_billable' => false,
        ]);

        (new JobBillingService())->markBillable($job, 75.0);

        $this->assertTrue($job->fresh()->is_billable);
        $this->assertEquals(75.0, $job->fresh()->billable_rate);

        Event::assertDispatched(JobMarkedBillable::class, fn ($e) => $e->job->id === $job->id);
    }

    public function test_generate_invoice_creates_invoice_and_links_to_job(): void
    {
        Event::fake();

        $company  = 41;
        $customer = Customer::factory()->create(['company_id' => $company]);
        $user     = User::factory()->create(['company_id' => $company]);

        $job = ServiceJob::factory()->create([
            'company_id'  => $company,
            'site_id'     => Site::factory()->create(['company_id' => $company])->id,
            'customer_id' => $customer->id,
            'created_by'  => $user->id,
            'is_billable' => true,
            'billable_rate' => 50.0,
            'date_start'  => now()->subHours(2),
            'date_end'    => now(),
        ]);

        $invoice = (new JobBillingService())->generateInvoice($job);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('draft', $invoice->status);
        $this->assertEquals($company, $invoice->company_id);
        $this->assertEquals($job->fresh()->invoice_id, $invoice->id);
        $this->assertNotNull($job->fresh()->invoiced_at);

        Event::assertDispatched(ServiceInvoiceGenerated::class,
            fn ($e) => $e->job->id === $job->id && $e->invoice->id === $invoice->id
        );
    }

    public function test_generate_invoice_throws_when_already_invoiced(): void
    {
        $company = 42;

        $invoice = Invoice::factory()->create(['company_id' => $company]);

        $job = ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => Site::factory()->create(['company_id' => $company])->id,
            'is_billable' => true,
            'invoice_id'  => $invoice->id,
        ]);

        $this->expectException(ValidationException::class);

        (new JobBillingService())->generateInvoice($job);
    }

    public function test_agreement_service_consumed_event_fired_on_billing_record(): void
    {
        Event::fake();

        $company  = 43;
        $customer = Customer::factory()->create(['company_id' => $company]);

        $agreement = ServiceAgreement::factory()->create([
            'company_id' => $company,
            'status'     => 'active',
        ]);

        $job = ServiceJob::factory()->create([
            'company_id'   => $company,
            'site_id'      => Site::factory()->create(['company_id' => $company])->id,
            'agreement_id' => $agreement->id,
        ]);

        (new JobBillingService())->recordAgreementConsumption($agreement, $job);

        Event::assertDispatched(AgreementServiceConsumed::class,
            fn ($e) => $e->agreement->id === $agreement->id && $e->job->id === $job->id
        );
    }

    public function test_invoiceable_stage_triggers_billing_on_stage_change(): void
    {
        Event::fake();

        $company  = 44;
        $customer = Customer::factory()->create(['company_id' => $company]);
        $user     = User::factory()->create(['company_id' => $company]);

        $from = JobStage::factory()->create([
            'company_id'     => $company,
            'stage_type'     => 'order',
            'is_invoiceable' => false,
        ]);

        $invoiceableStage = JobStage::factory()->create([
            'company_id'     => $company,
            'stage_type'     => 'order',
            'is_invoiceable' => true,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id'    => $company,
            'site_id'       => Site::factory()->create(['company_id' => $company])->id,
            'customer_id'   => $customer->id,
            'created_by'    => $user->id,
            'stage_id'      => $from->id,
            'is_billable'   => true,
            'billable_rate' => 60.0,
            'date_start'    => now()->subHour(),
            'date_end'      => now(),
        ]);

        (new JobStageService())->transition($job, $invoiceableStage);

        Event::assertDispatched(JobReadyForInvoice::class, fn ($e) => $e->job->id === $job->id);
    }

    public function test_unbilled_scope_returns_billable_jobs_without_invoice(): void
    {
        $company = 45;

        $site = Site::factory()->create(['company_id' => $company]);

        $invoice = Invoice::factory()->create(['company_id' => $company]);

        ServiceJob::factory()->create([
            'company_id'  => $company,
            'site_id'     => $site->id,
            'is_billable' => true,
            'invoice_id'  => null,
        ]);

        ServiceJob::factory()->create([
            'company_id'  => $company,
            'site_id'     => $site->id,
            'is_billable' => true,
            'invoice_id'  => $invoice->id,
        ]);

        ServiceJob::factory()->create([
            'company_id'  => $company,
            'site_id'     => $site->id,
            'is_billable' => false,
        ]);

        $this->actingAs(User::factory()->create(['company_id' => $company]));

        $this->assertSame(1, ServiceJob::unbilled()->count());
    }
}
