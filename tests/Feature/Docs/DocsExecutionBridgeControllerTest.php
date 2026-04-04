<?php

declare(strict_types=1);

namespace Tests\Feature\Docs;

use App\Models\Premises\DocumentInjectionRule;
use App\Models\Premises\FacilityDocument;
use App\Models\Premises\JobInjectedDocument;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Services\Docs\DocsExecutionBridgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DocsExecutionBridgeControllerTest extends TestCase
{
    use RefreshDatabase;

    // ── GET /dashboard/docs/jobs/{job} ────────────────────────────────────────

    public function test_for_job_returns_injected_documents(): void
    {
        $user = User::factory()->create(['company_id' => 1]);
        $job  = ServiceJob::factory()->create(['company_id' => 1]);

        $doc = FacilityDocument::factory()->create([
            'company_id'       => 1,
            'title'            => 'Fire Safety Procedure',
            'document_category' => 'safety',
            'status'           => 'valid',
        ]);

        JobInjectedDocument::create([
            'job_id'           => $job->id,
            'document_id'      => $doc->id,
            'injection_source' => 'rule',
            'is_mandatory'     => true,
            'injected_at'      => now(),
        ]);

        $this->actingAs($user)
            ->getJson(route('dashboard.docs.jobs.documents', $job))
            ->assertOk()
            ->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data');
    }

    public function test_for_job_forbidden_for_other_company(): void
    {
        $user = User::factory()->create(['company_id' => 2]);
        $job  = ServiceJob::factory()->create(['company_id' => 1]);

        $this->actingAs($user)
            ->getJson(route('dashboard.docs.jobs.documents', $job))
            ->assertForbidden();
    }

    // ── POST /dashboard/docs/acknowledge ─────────────────────────────────────

    public function test_acknowledge_marks_document_acknowledged(): void
    {
        $user = User::factory()->create(['company_id' => 1]);
        $job  = ServiceJob::factory()->create(['company_id' => 1]);

        $doc = FacilityDocument::factory()->create([
            'company_id' => 1,
            'status'     => 'valid',
        ]);

        $pivot = JobInjectedDocument::create([
            'job_id'           => $job->id,
            'document_id'      => $doc->id,
            'injection_source' => 'rule',
            'is_mandatory'     => true,
            'injected_at'      => now(),
        ]);

        $this->actingAs($user)
            ->postJson(route('dashboard.docs.acknowledge'), [
                'document_id'  => $doc->id,
                'context_id'   => $job->id,
                'context_type' => 'job',
            ])
            ->assertOk()
            ->assertJson(['status' => 'acknowledged']);

        $this->assertDatabaseHas('job_injected_documents', [
            'id'              => $pivot->id,
            'acknowledged_by' => $user->id,
        ]);
    }

    // ── GET /dashboard/docs/search ────────────────────────────────────────────

    public function test_search_returns_results(): void
    {
        $user = User::factory()->create(['company_id' => 1]);

        FacilityDocument::factory()->create([
            'company_id'       => 1,
            'title'            => 'HVAC Maintenance SOP',
            'document_category' => 'sop',
            'status'           => 'valid',
        ]);

        $this->actingAs($user)
            ->getJson(route('dashboard.docs.search', ['q' => 'HVAC maintenance']))
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_search_requires_minimum_query_length(): void
    {
        $user = User::factory()->create(['company_id' => 1]);

        $this->actingAs($user)
            ->getJson(route('dashboard.docs.search', ['q' => 'a']))
            ->assertUnprocessable();
    }

    // ── GET /dashboard/docs/rules ─────────────────────────────────────────────

    public function test_rules_returns_injection_rules(): void
    {
        $user = User::factory()->create(['company_id' => 1]);

        $doc = FacilityDocument::factory()->create([
            'company_id' => 1,
            'status'     => 'valid',
        ]);

        DocumentInjectionRule::create([
            'company_id'  => 1,
            'rule_type'   => 'job_type',
            'rule_value'  => '5',
            'document_id' => $doc->id,
            'is_mandatory' => true,
            'is_active'   => true,
        ]);

        $this->actingAs($user)
            ->getJson(route('dashboard.docs.rules.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
