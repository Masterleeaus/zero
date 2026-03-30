<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work\Checklist;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_creation_is_scoped_to_company(): void
    {
        $user = User::factory()->create(['company_id' => 7]);
        $this->actingAs($user);

        $response = $this->post(route('dashboard.work.sites.store'), [
            'name' => 'Depot North',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('sites', [
            'name'       => 'Depot North',
            'company_id' => 7,
        ]);
    }

    public function test_service_job_creation_requires_site_and_scopes_company(): void
    {
        $user = User::factory()->create(['company_id' => 7]);
        $site = Site::factory()->create(['company_id' => 7]);

        $this->actingAs($user);

        $response = $this->post(route('dashboard.work.service-jobs.store'), [
            'site_id' => $site->id,
            'title'   => 'Boiler service',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('service_jobs', [
            'title'      => 'Boiler service',
            'site_id'    => $site->id,
            'company_id' => 7,
        ]);
    }

    public function test_checklist_creation_links_job_and_scopes_company(): void
    {
        $user = User::factory()->create(['company_id' => 7]);
        $site = Site::factory()->create(['company_id' => 7]);
        $job = ServiceJob::factory()->create(['company_id' => 7, 'site_id' => $site->id]);

        $this->actingAs($user);

        $response = $this->post(route('dashboard.work.checklists.store'), [
            'service_job_id' => $job->id,
            'title'          => 'Inspect filters',
            'is_completed'   => false,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('checklists', [
            'title'          => 'Inspect filters',
            'service_job_id' => $job->id,
            'company_id'     => 7,
        ]);
    }
}
