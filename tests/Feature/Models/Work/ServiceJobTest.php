<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Work;

use App\Models\Crm\Customer;
use App\Models\Money\Quote;
use App\Models\User;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_jobs_are_scoped_to_company(): void
    {
        $user = User::factory()->create(['company_id' => 11]);

        $siteA = Site::factory()->create(['company_id' => 11]);
        $siteB = Site::factory()->create(['company_id' => 12]);

        ServiceJob::factory()->count(2)->create([
            'company_id' => 11,
            'site_id'    => $siteA->id,
        ]);
        ServiceJob::factory()->create([
            'company_id' => 12,
            'site_id'    => $siteB->id,
        ]);

        $this->actingAs($user);

        $this->assertSame(2, ServiceJob::count());
    }

    public function test_status_transitions(): void
    {
        $job = ServiceJob::factory()->create([
            'company_id' => 9,
            'site_id'    => Site::factory()->create(['company_id' => 9])->id,
            'status'     => 'scheduled',
        ]);

        $job->update(['status' => 'in_progress']);
        $this->assertSame('in_progress', $job->fresh()->status);

        $job->update(['status' => 'completed']);
        $this->assertSame('completed', $job->fresh()->status);

        $job->update(['status' => 'cancelled']);
        $this->assertSame('cancelled', $job->fresh()->status);
    }

    public function test_relationships_to_site_quote_and_agreement(): void
    {
        $company = 7;
        $site = Site::factory()->create(['company_id' => $company]);
        $quote = Quote::factory()->create([
            'company_id' => $company,
            'customer_id'=> Customer::factory(['company_id' => $company]),
        ]);
        $agreement = ServiceAgreement::factory()->create([
            'company_id'  => $company,
            'customer_id' => $quote->customer_id,
            'site_id'     => $site->id,
            'quote_id'    => $quote->id,
        ]);

        $job = ServiceJob::factory()->create([
            'company_id'    => $company,
            'site_id'       => $site->id,
            'quote_id'      => $quote->id,
            'customer_id'   => $quote->customer_id,
            'agreement_id'  => $agreement->id,
        ]);

        $this->assertTrue($job->site->is($site));
        $this->assertTrue($job->quote->is($quote));
        $this->assertTrue($job->agreement->is($agreement));
    }

    public function test_unassigned_scope_returns_jobs_without_assignee(): void
    {
        $company = 8;
        $site = Site::factory()->create(['company_id' => $company]);
        ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => $site->id,
            'assigned_user_id' => null,
        ]);
        ServiceJob::factory()->create([
            'company_id' => $company,
            'site_id'    => $site->id,
            'assigned_user_id' => User::factory()->create(['company_id' => $company])->id,
        ]);

        $this->assertCount(1, ServiceJob::unassigned()->get());
    }
}
