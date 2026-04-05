<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TITAN OMNI — Pass 02 — Voice tables
 *
 * Creates:
 *   omni_voice_calls         — Inbound + outbound call records
 *   omni_call_logs           — Timestamped voice call events (append-only)
 *   omni_callback_schedules  — Pending customer callback requests
 *
 * Donor source: CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED
 * All tables enforce company_id.
 * omni_call_logs is append-only; no UPDATE or DELETE permitted by application contract.
 * omni_voice_calls.started_at and ended_at are set-once (immutability contract).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── omni_voice_calls ──────────────────────────────────────────────────
        if (! Schema::hasTable('omni_voice_calls')) {
            Schema::create('omni_voice_calls', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('conversation_id')->nullable()->index();
                $table->unsignedBigInteger('channel_bridge_id')->nullable()->index();
                $table->unsignedBigInteger('omni_customer_id')->nullable()->index();
                // direction: inbound | outbound
                $table->string('direction', 20)->default('inbound')->index();
                // provider: twilio | vapi | bland | other
                $table->string('provider', 50)->default('twilio')->index();
                $table->string('provider_call_id')->nullable()->index();
                $table->string('from_number', 32)->nullable()->index();
                $table->string('to_number', 32)->nullable()->index();
                // status: queued | ringing | in-progress | completed | failed | busy | no-answer
                $table->string('status', 50)->default('queued')->index();
                $table->unsignedInteger('duration_seconds')->nullable();
                $table->string('recording_url')->nullable();
                $table->longText('transcript')->nullable();
                // Set-once timestamps — never overwritten (immutability contract)
                $table->timestamp('started_at')->nullable()->index();
                $table->timestamp('ended_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'direction', 'started_at']);
            });
        }

        // ── omni_call_logs ───────────────────────────────────────────────────
        // Append-only. No UPDATE, no DELETE permitted.
        if (! Schema::hasTable('omni_call_logs')) {
            Schema::create('omni_call_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('voice_call_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('event_type', 100)->index();
                $table->json('payload')->nullable();
                // occurred_at only — no updated_at (append-only)
                $table->timestamp('occurred_at')->nullable()->index();

                $table->index(['voice_call_id', 'occurred_at']);
                $table->index(['company_id', 'event_type', 'occurred_at']);
            });
        }

        // ── omni_callback_schedules ──────────────────────────────────────────
        if (! Schema::hasTable('omni_callback_schedules')) {
            Schema::create('omni_callback_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('omni_customer_id')->nullable()->index();
                $table->unsignedBigInteger('assigned_to')->nullable()->index();
                $table->string('phone_number', 32)->nullable();
                $table->text('notes')->nullable();
                // status: pending | handled | cancelled
                $table->string('status', 50)->default('pending')->index();
                $table->timestamp('scheduled_at')->nullable()->index();
                $table->timestamp('handled_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status', 'scheduled_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_callback_schedules');
        Schema::dropIfExists('omni_call_logs');
        Schema::dropIfExists('omni_voice_calls');
    }
};
