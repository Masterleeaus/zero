<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Trust;

use App\Events\Trust\ChainTamperingDetected;
use App\Models\Trust\TrustLedgerEntry;
use App\Models\User;
use App\Services\Trust\TrustLedgerService;
use App\Services\Trust\TrustVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TrustVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private TrustVerificationService $service;
    private TrustLedgerService $ledger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ledger  = app(TrustLedgerService::class);
        $this->service = app(TrustVerificationService::class);
    }

    public function test_verify_entry_returns_true_for_valid_entry(): void
    {
        $job   = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 10]);
        $entry = $this->ledger->record('job_completed', $job, ['status' => 'done']);

        $this->assertTrue($this->service->verifyEntry($entry));
    }

    public function test_detect_tampering_returns_empty_when_chain_intact(): void
    {
        $job = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 11]);
        $this->ledger->record('job_completed', $job, []);

        $tampered = $this->service->detectTampering(11);

        $this->assertEmpty($tampered);
    }

    public function test_detect_tampering_returns_entries_when_hash_corrupted(): void
    {
        Event::fake([ChainTamperingDetected::class]);

        $job   = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 12]);
        $entry = $this->ledger->record('job_completed', $job, []);

        DB::table('trust_ledger_entries')
            ->where('id', $entry->id)
            ->update(['chain_hash' => 'bad_hash']);

        $tampered = $this->service->detectTampering(12);

        $this->assertCount(1, $tampered);
        $this->assertEquals($entry->id, $tampered[0]['id']);
        Event::assertDispatched(ChainTamperingDetected::class, function ($event) {
            return $event->companyId === 12;
        });
    }

    public function test_generate_compliance_proof_marks_chain_intact(): void
    {
        $job = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 13]);
        $this->ledger->record('job_completed', $job, ['detail' => 'complete']);

        $proof = $this->service->generateComplianceProof($job);

        $this->assertTrue($proof['chain_intact']);
        $this->assertCount(1, $proof['entries']);
        $this->assertTrue($proof['entries'][0]['verified']);
    }

    public function test_generate_compliance_proof_marks_chain_not_intact_when_tampered(): void
    {
        Event::fake([ChainTamperingDetected::class]);

        $job   = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 14]);
        $entry = $this->ledger->record('job_completed', $job, []);

        DB::table('trust_ledger_entries')
            ->where('id', $entry->id)
            ->update(['chain_hash' => 'corrupted']);

        $proof = $this->service->generateComplianceProof($job);

        $this->assertFalse($proof['chain_intact']);
        $this->assertFalse($proof['entries'][0]['verified']);
    }
}
