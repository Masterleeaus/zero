<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TITAN OMNI — Pass 03 — Automation + Sequence + Handoff tables
 *
 * Creates:
 *   omni_sequences          — Multi-step outreach / nurture sequences
 *   omni_sequence_steps     — Individual steps within a sequence
 *   omni_sequence_runs      — Execution records per customer per sequence
 *   omni_automations        — Trigger-based automation rules
 *   omni_automation_actions — Actions executed by an automation
 *   omni_overlay_bindings   — Per-conversation overlay / widget configuration
 *   omni_handoff_rules      — Rules for transferring conversations to humans
 *   omni_message_attachments — Media/file attachments on messages
 *
 * All tables enforce company_id for tenant isolation.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── omni_sequences ───────────────────────────────────────────────────
        if (! Schema::hasTable('omni_sequences')) {
            Schema::create('omni_sequences', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('channel_type', 50)->default('whatsapp')->index();
                // status: draft | active | paused | archived
                $table->string('status', 50)->default('draft')->index();
                $table->unsignedInteger('step_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status']);
            });
        }

        // ── omni_sequence_steps ──────────────────────────────────────────────
        if (! Schema::hasTable('omni_sequence_steps')) {
            Schema::create('omni_sequence_steps', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sequence_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedSmallInteger('step_order')->default(0);
                // type: message | delay | condition | tag | webhook
                $table->string('step_type', 50)->default('message');
                $table->text('content')->nullable();
                $table->string('content_type', 50)->default('text');
                // Delay before executing this step (in minutes)
                $table->unsignedInteger('delay_minutes')->default(0);
                $table->json('conditions')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['sequence_id', 'step_order']);
                $table->index(['company_id', 'sequence_id']);
            });
        }

        // ── omni_sequence_runs ───────────────────────────────────────────────
        if (! Schema::hasTable('omni_sequence_runs')) {
            Schema::create('omni_sequence_runs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sequence_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('omni_customer_id')->index();
                $table->unsignedBigInteger('current_step_id')->nullable()->index();
                // status: active | paused | completed | cancelled | failed
                $table->string('status', 50)->default('active')->index();
                $table->unsignedSmallInteger('steps_completed')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('next_step_at')->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['sequence_id', 'status']);
                $table->index(['company_id', 'omni_customer_id']);
                $table->unique(['sequence_id', 'omni_customer_id']);
            });
        }

        // ── omni_automations ─────────────────────────────────────────────────
        if (! Schema::hasTable('omni_automations')) {
            Schema::create('omni_automations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->string('name');
                $table->text('description')->nullable();
                // trigger_type: message_received | conversation_started | conversation_resolved
                //               keyword_match | no_reply | campaign_delivered | webhook
                $table->string('trigger_type', 80)->index();
                $table->json('trigger_conditions')->nullable();
                $table->string('channel_scope', 50)->default('all');
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('run_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'is_active', 'trigger_type']);
            });
        }

        // ── omni_automation_actions ──────────────────────────────────────────
        if (! Schema::hasTable('omni_automation_actions')) {
            Schema::create('omni_automation_actions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('automation_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedSmallInteger('action_order')->default(0);
                // action_type: send_message | assign_agent | add_tag | start_sequence
                //              fire_webhook | create_crm_note | resolve_conversation
                $table->string('action_type', 80);
                $table->json('action_config')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['automation_id', 'action_order']);
            });
        }

        // ── omni_overlay_bindings ────────────────────────────────────────────
        if (! Schema::hasTable('omni_overlay_bindings')) {
            Schema::create('omni_overlay_bindings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->unsignedBigInteger('conversation_id')->nullable()->index();
                // surface: web_embed | portal | mobile | api
                $table->string('surface', 50)->default('web_embed');
                $table->string('binding_key')->index();
                $table->json('config')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamp('bound_at')->nullable();
                $table->timestamp('unbound_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'binding_key']);
                $table->index(['company_id', 'surface', 'is_active']);
            });
        }

        // ── omni_handoff_rules ───────────────────────────────────────────────
        if (! Schema::hasTable('omni_handoff_rules')) {
            Schema::create('omni_handoff_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->string('name');
                // trigger: keyword | sentiment | no_ai_response | escalation_requested | timeout
                $table->string('trigger_type', 80)->index();
                $table->json('trigger_conditions')->nullable();
                // target: user_id | team_id | queue | null (= first available)
                $table->string('handoff_target_type', 50)->nullable();
                $table->unsignedBigInteger('handoff_target_id')->nullable();
                $table->string('channel_scope', 50)->default('all');
                $table->unsignedSmallInteger('priority')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'is_active', 'trigger_type']);
            });
        }

        // ── omni_message_attachments ─────────────────────────────────────────
        if (! Schema::hasTable('omni_message_attachments')) {
            Schema::create('omni_message_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('message_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                // type: image | video | audio | document | sticker | location | contact
                $table->string('attachment_type', 50)->default('document')->index();
                $table->string('media_url');
                $table->string('media_type', 80)->nullable();
                $table->unsignedBigInteger('media_size_bytes')->nullable();
                $table->string('file_name')->nullable();
                $table->string('caption')->nullable();
                $table->string('external_media_id')->nullable()->index();
                $table->json('metadata')->nullable();
                // Append-only — created_at only, no updated_at
                $table->timestamp('created_at')->nullable();

                $table->index(['message_id', 'attachment_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_message_attachments');
        Schema::dropIfExists('omni_handoff_rules');
        Schema::dropIfExists('omni_overlay_bindings');
        Schema::dropIfExists('omni_automation_actions');
        Schema::dropIfExists('omni_automations');
        Schema::dropIfExists('omni_sequence_runs');
        Schema::dropIfExists('omni_sequence_steps');
        Schema::dropIfExists('omni_sequences');
    }
};
