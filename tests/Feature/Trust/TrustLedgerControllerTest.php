<?php

declare(strict_types=1);

namespace Tests\Feature\Trust;

use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Services\Trust\TrustLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustLedgerControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsCompanyUser(int $companyId = 1): User
    {
        $user = User::factory()->create(['company_id' => $companyId]);
        $this->actingAs($user);

        return $user;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // chain
    // ─────────────────────────────────────────────────────────────────────────

    public function test_chain_returns_entries_for_subject(): void
    {
        $user   = $this->actingAsCompanyUser(1);
        $job    = ServiceJob::factory()->create(['company_id' => 1]);
        $ledger = app(TrustLedgerService::class);
        $ledger->record('job_completed', $job, ['note' => 'ok'], $user);

        $this->getJson(route('dashboard.trust.chain', [
            'subject_type' => \App\Models\Work\ServiceJob::class,
            'subject_id'   => $job->id,
        ]))->assertOk()
           ->assertJsonStructure(['entries']);
    }

    public function test_chain_rejects_unauthenticated(): void
    {
        $this->getJson(route('dashboard.trust.chain', [
            'subject_type' => \App\Models\Work\ServiceJob::class,
            'subject_id'   => 1,
        ]))->assertUnauthorized();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // verify
    // ─────────────────────────────────────────────────────────────────────────

    public function test_verify_returns_chain_intact_for_valid_chain(): void
    {
        $user   = $this->actingAsCompanyUser(2);
        $job    = ServiceJob::factory()->create(['company_id' => 2]);
        $ledger = app(TrustLedgerService::class);
        $ledger->record('job_completed', $job, [], $user);

        $this->getJson(route('dashboard.trust.verify', [
            'subject_type' => \App\Models\Work\ServiceJob::class,
            'subject_id'   => $job->id,
        ]))->assertOk()
           ->assertJson(['chain_intact' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // proof
    // ─────────────────────────────────────────────────────────────────────────

    public function test_proof_returns_compliance_document(): void
    {
        $user   = $this->actingAsCompanyUser(3);
        $job    = ServiceJob::factory()->create(['company_id' => 3]);
        $ledger = app(TrustLedgerService::class);
        $ledger->record('job_completed', $job, ['detail' => 'test'], $user);

        $this->getJson(route('dashboard.trust.proof', [
            'subject_type' => \App\Models\Work\ServiceJob::class,
            'subject_id'   => $job->id,
        ]))->assertOk()
           ->assertJsonStructure([
               'subject_type',
               'subject_id',
               'entry_count',
               'chain_intact',
               'generated_at',
               'entries',
           ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // seal
    // ─────────────────────────────────────────────────────────────────────────

    public function test_seal_creates_a_chain_seal(): void
    {
        $user   = $this->actingAsCompanyUser(4);
        $job    = ServiceJob::factory()->create(['company_id' => 4]);
        $ledger = app(TrustLedgerService::class);
        $ledger->record('job_completed', $job, [], $user);

        $this->postJson(route('dashboard.trust.seal'))
             ->assertCreated()
             ->assertJsonStructure(['seal_hash', 'entry_count', 'sealed_at']);
    }

    public function test_chain_endpoint_rejects_disallowed_subject_type(): void
    {
        $this->actingAsCompanyUser(5);

        $this->getJson(route('dashboard.trust.chain', [
            'subject_type' => 'App\\Models\\User',
            'subject_id'   => 1,
        ]))->assertNotFound();
    }
}
