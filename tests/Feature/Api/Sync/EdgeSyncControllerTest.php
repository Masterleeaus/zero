<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Sync;

use App\Events\Sync\EdgeBatchSynced;
use App\Events\Sync\EdgeConflictDetected;
use App\Models\Sync\EdgeDeviceSession;
use App\Models\Sync\EdgeSyncConflict;
use App\Models\Sync\EdgeSyncLog;
use App\Models\Sync\EdgeSyncQueue;
use App\Models\User;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * MODULE 05 — TitanEdgeSync API feature tests.
 *
 * Covers:
 *  - POST /api/sync/register  — device registration
 *  - POST /api/sync/push      — batch ingestion
 *  - GET  /api/sync/pull      — delta fetch
 *  - POST /api/sync/acknowledge — cursor advance
 *  - GET  /api/sync/conflicts — list conflicts
 *  - POST /api/sync/conflicts/{id}/resolve — resolve conflict
 */
class EdgeSyncControllerTest extends TestCase
{
    use RefreshDatabase;

    // ── register ──────────────────────────────────────────────────────────────

    public function test_register_creates_device_session(): void
    {
        $user = User::factory()->create(['company_id' => 90]);

        $this->actingAs($user, 'api')
            ->postJson('/api/sync/register', [
                'device_id'   => 'device-abc-123',
                'device_name' => 'Technician iPad',
                'platform'    => 'ios',
            ])
            ->assertOk()
            ->assertJsonStructure(['status', 'device_id', 'sync_cursor', 'platform']);

        $this->assertDatabaseHas('edge_device_sessions', [
            'user_id'   => $user->id,
            'device_id' => 'device-abc-123',
            'platform'  => 'ios',
        ]);
    }

    public function test_register_requires_device_id(): void
    {
        $user = User::factory()->create(['company_id' => 91]);

        $this->actingAs($user, 'api')
            ->postJson('/api/sync/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id']);
    }

    public function test_register_requires_valid_platform(): void
    {
        $user = User::factory()->create(['company_id' => 92]);

        $this->actingAs($user, 'api')
            ->postJson('/api/sync/register', [
                'device_id' => 'dev-xyz',
                'platform'  => 'unsupported_platform',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['platform']);
    }

    // ── push ──────────────────────────────────────────────────────────────────

    public function test_push_accepts_valid_batch(): void
    {
        Event::fake([EdgeBatchSynced::class]);

        $user = User::factory()->create(['company_id' => 93]);

        // Ensure a device session exists.
        EdgeDeviceSession::create([
            'company_id' => 93,
            'user_id'    => $user->id,
            'device_id'  => 'dev-push-test',
            'platform'   => 'android',
        ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/sync/push', [
                'device_id'  => 'dev-push-test',
                'operations' => [
                    [
                        'operation_type'    => 'job_update',
                        'subject_type'      => 'service_job',
                        'subject_id'        => 9999,
                        'payload'           => ['notes' => 'Updated offline'],
                        'client_created_at' => now()->toIso8601String(),
                    ],
                ],
            ])
            ->assertStatus(202)
            ->assertJsonStructure(['batch_id', 'accepted', 'conflicts', 'failed']);

        $this->assertDatabaseHas('edge_sync_queues', [
            'user_id'        => $user->id,
            'device_id'      => 'dev-push-test',
            'operation_type' => 'job_update',
        ]);

        $this->assertDatabaseHas('edge_sync_log', [
            'user_id'   => $user->id,
            'device_id' => 'dev-push-test',
        ]);
    }

    public function test_push_requires_operations_array(): void
    {
        $user = User::factory()->create(['company_id' => 94]);

        $this->actingAs($user, 'api')
            ->postJson('/api/sync/push', [
                'device_id' => 'dev-x',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['operations']);
    }

    public function test_push_rejects_invalid_operation_type(): void
    {
        $user = User::factory()->create(['company_id' => 95]);

        $this->actingAs($user, 'api')
            ->postJson('/api/sync/push', [
                'device_id'  => 'dev-y',
                'operations' => [
                    [
                        'operation_type' => 'invalid_type',
                        'payload'        => [],
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['operations.0.operation_type']);
    }

    // ── pull ──────────────────────────────────────────────────────────────────

    public function test_pull_returns_delta_for_registered_device(): void
    {
        $user = User::factory()->create(['company_id' => 96]);

        EdgeDeviceSession::create([
            'company_id'  => 96,
            'user_id'     => $user->id,
            'device_id'   => 'dev-pull-test',
            'sync_cursor' => 0,
        ]);

        $this->actingAs($user, 'api')
            ->getJson('/api/sync/pull?device_id=dev-pull-test')
            ->assertOk()
            ->assertJsonStructure(['since', 'jobs', 'checklist_runs', 'inspection_instances']);
    }

    public function test_pull_returns_404_for_unknown_device(): void
    {
        $user = User::factory()->create(['company_id' => 97]);

        $this->actingAs($user, 'api')
            ->getJson('/api/sync/pull?device_id=unknown-device')
            ->assertNotFound();
    }

    // ── acknowledge ───────────────────────────────────────────────────────────

    public function test_acknowledge_advances_cursor(): void
    {
        $user = User::factory()->create(['company_id' => 98]);

        EdgeDeviceSession::create([
            'company_id'  => 98,
            'user_id'     => $user->id,
            'device_id'   => 'dev-ack-test',
            'sync_cursor' => 5,
        ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/sync/acknowledge', [
                'device_id' => 'dev-ack-test',
                'cursor'    => 42,
            ])
            ->assertOk()
            ->assertJson(['status' => 'acknowledged', 'cursor' => 42]);

        $this->assertDatabaseHas('edge_device_sessions', [
            'user_id'     => $user->id,
            'device_id'   => 'dev-ack-test',
            'sync_cursor' => 42,
        ]);
    }

    // ── conflicts ─────────────────────────────────────────────────────────────

    public function test_conflicts_returns_paginated_list(): void
    {
        $user = User::factory()->create(['company_id' => 99]);

        $this->actingAs($user, 'api')
            ->getJson('/api/sync/conflicts')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'total']);
    }

    // ── resolve conflict ──────────────────────────────────────────────────────

    public function test_resolve_conflict_applies_strategy(): void
    {
        Event::fake();

        $user = User::factory()->create(['company_id' => 100]);

        $queueItem = EdgeSyncQueue::create([
            'company_id'     => 100,
            'user_id'        => $user->id,
            'device_id'      => 'dev-conflict',
            'operation_type' => 'job_update',
            'payload'        => ['notes' => 'offline notes'],
            'status'         => 'conflict',
        ]);

        $conflict = EdgeSyncConflict::create([
            'sync_queue_id' => $queueItem->id,
            'conflict_type' => 'version_mismatch',
            'server_state'  => ['notes' => 'server notes'],
            'client_state'  => ['notes' => 'offline notes'],
        ]);

        $this->actingAs($user, 'api')
            ->postJson("/api/sync/conflicts/{$conflict->id}/resolve", [
                'strategy' => 'server_wins',
            ])
            ->assertOk()
            ->assertJson(['status' => 'resolved', 'strategy' => 'server_wins']);

        $this->assertDatabaseHas('edge_sync_conflicts', [
            'id'          => $conflict->id,
            'resolved_by' => 'system',
        ]);
    }

    public function test_resolve_conflict_requires_valid_strategy(): void
    {
        $user = User::factory()->create(['company_id' => 101]);

        $this->actingAs($user, 'api')
            ->postJson('/api/sync/conflicts/999/resolve', [
                'strategy' => 'invalid_strategy',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['strategy']);
    }

    // ── auth guard ────────────────────────────────────────────────────────────

    public function test_sync_routes_require_authentication(): void
    {
        $this->postJson('/api/sync/register')->assertUnauthorized();
        $this->postJson('/api/sync/push')->assertUnauthorized();
        $this->getJson('/api/sync/pull')->assertUnauthorized();
        $this->postJson('/api/sync/acknowledge')->assertUnauthorized();
        $this->getJson('/api/sync/conflicts')->assertUnauthorized();
        $this->postJson('/api/sync/conflicts/1/resolve')->assertUnauthorized();
    }
}
