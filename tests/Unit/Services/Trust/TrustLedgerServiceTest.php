<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Trust;

use App\Events\Trust\LedgerEntryRecorded;
use App\Exceptions\Trust\ImmutableRecordException;
use App\Models\Trust\TrustLedgerEntry;
use App\Models\User;
use App\Services\Trust\TrustLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TrustLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    private TrustLedgerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TrustLedgerService::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // record()
    // ─────────────────────────────────────────────────────────────────────────

    public function test_record_creates_ledger_entry(): void
    {
        Event::fake([LedgerEntryRecorded::class]);

        $user = User::factory()->create(['company_id' => 1]);
        $job  = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 1]);

        $entry = $this->service->record('job_completed', $job, ['note' => 'done'], $user);

        $this->assertInstanceOf(TrustLedgerEntry::class, $entry);
        $this->assertDatabaseHas('trust_ledger_entries', [
            'entry_type'   => 'job_completed',
            'subject_type' => \App\Models\Work\ServiceJob::class,
            'subject_id'   => $job->id,
            'actor_id'     => $user->id,
        ]);
        Event::assertDispatched(LedgerEntryRecorded::class);
    }

    public function test_first_entry_has_null_parent_hash(): void
    {
        $job = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 2]);

        $entry = $this->service->record('job_completed', $job, []);

        $this->assertNull($entry->parent_hash);
    }

    public function test_second_entry_links_to_first(): void
    {
        $job = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 3]);

        $first  = $this->service->record('job_completed', $job, []);
        $second = $this->service->record('job_completed', $job, []);

        $this->assertEquals($first->chain_hash, $second->parent_hash);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // buildChainHash()
    // ─────────────────────────────────────────────────────────────────────────

    public function test_build_chain_hash_is_deterministic(): void
    {
        $hash1 = $this->service->buildChainHash('parent', 'job_completed', 'Model', '1', '5', [], '2026-01-01T00:00:00+00:00');
        $hash2 = $this->service->buildChainHash('parent', 'job_completed', 'Model', '1', '5', [], '2026-01-01T00:00:00+00:00');

        $this->assertSame($hash1, $hash2);
    }

    public function test_build_chain_hash_changes_when_payload_changes(): void
    {
        $hash1 = $this->service->buildChainHash(null, 'job_completed', 'Model', '1', '5', ['a' => 1], '2026-01-01T00:00:00+00:00');
        $hash2 = $this->service->buildChainHash(null, 'job_completed', 'Model', '1', '5', ['a' => 2], '2026-01-01T00:00:00+00:00');

        $this->assertNotSame($hash1, $hash2);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // getChain() / verifyChain()
    // ─────────────────────────────────────────────────────────────────────────

    public function test_get_chain_returns_all_entries_for_subject(): void
    {
        $job = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 4]);

        $this->service->record('job_completed', $job, []);
        $this->service->record('job_completed', $job, []);

        $chain = $this->service->getChain($job);

        $this->assertCount(2, $chain);
    }

    public function test_verify_chain_returns_true_for_valid_chain(): void
    {
        $job = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 5]);

        $this->service->record('job_completed', $job, ['ok' => true]);
        $this->service->record('job_completed', $job, ['ok' => true]);

        $this->assertTrue($this->service->verifyChain($job));
    }

    public function test_verify_entry_returns_false_after_hash_manipulation(): void
    {
        $job   = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 6]);
        $entry = $this->service->record('job_completed', $job, []);

        // Directly corrupt the stored hash (bypassing the model guard).
        \Illuminate\Support\Facades\DB::table('trust_ledger_entries')
            ->where('id', $entry->id)
            ->update(['chain_hash' => 'tampered_hash_value']);

        $entry->refresh();
        $this->assertFalse($this->service->verifyEntry($entry));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Immutability
    // ─────────────────────────────────────────────────────────────────────────

    public function test_updating_ledger_entry_throws_immutable_exception(): void
    {
        $this->expectException(ImmutableRecordException::class);

        $job   = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 7]);
        $entry = $this->service->record('job_completed', $job, []);

        $entry->entry_type = 'override_applied';
        $entry->save();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // sealChain()
    // ─────────────────────────────────────────────────────────────────────────

    public function test_seal_chain_creates_seal_record(): void
    {
        $job = \App\Models\Work\ServiceJob::factory()->create(['company_id' => 8]);
        $this->service->record('job_completed', $job, []);

        $seal = $this->service->sealChain(8);

        $this->assertDatabaseHas('trust_chain_seals', [
            'company_id'  => 8,
            'entry_count' => 1,
        ]);
        $this->assertNotEmpty($seal->seal_hash);
    }
}
