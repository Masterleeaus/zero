<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULE 05 — TitanEdgeSync
 *
 * Creates all tables required for the offline-first sync engine:
 *  - edge_sync_queues      — incoming operations from devices
 *  - edge_sync_conflicts   — conflict records per queue item
 *  - edge_device_sessions  — registered device/session state
 *  - edge_sync_log         — per-batch audit log
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── edge_sync_queues ─────────────────────────────────────────────────
        Schema::create('edge_sync_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('device_id', 100)->index();
            $table->unsignedBigInteger('user_id')->index();

            $table->string('operation_type', 60);   // job_update|checklist_response|inspection_response|evidence_upload|signature_capture|job_complete
            $table->string('subject_type', 150)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->json('payload');
            $table->timestamp('client_created_at')->nullable();

            $table->string('status', 30)->default('pending'); // pending|processing|synced|conflict|failed
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'device_id', 'status']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('client_created_at');
        });

        // ── edge_sync_conflicts ──────────────────────────────────────────────
        Schema::create('edge_sync_conflicts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sync_queue_id');
            $table->string('conflict_type', 60); // field_collision|version_mismatch|deleted_subject|concurrent_edit
            $table->json('server_state')->nullable();
            $table->json('client_state')->nullable();
            $table->string('resolved_by', 30)->nullable(); // user|system|ai
            $table->json('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('sync_queue_id')
                ->references('id')
                ->on('edge_sync_queues')
                ->cascadeOnDelete();

            $table->index('sync_queue_id');
        });

        // ── edge_device_sessions ─────────────────────────────────────────────
        Schema::create('edge_device_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('device_id', 100);
            $table->string('device_name', 200)->nullable();
            $table->string('platform', 20)->default('pwa'); // ios|android|web|pwa
            $table->timestamp('last_sync_at')->nullable();
            $table->unsignedBigInteger('sync_cursor')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // device_id is only unique per user (client-generated UUID)
            $table->unique(['user_id', 'device_id']);
            $table->index(['company_id', 'is_active']);
        });

        // ── edge_sync_log ────────────────────────────────────────────────────
        Schema::create('edge_sync_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('device_id', 100)->index();
            $table->uuid('batch_id')->unique();
            $table->unsignedInteger('operations_count')->default(0);
            $table->unsignedInteger('conflicts_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edge_sync_log');
        Schema::dropIfExists('edge_device_sessions');
        Schema::dropIfExists('edge_sync_conflicts');
        Schema::dropIfExists('edge_sync_queues');
    }
};
