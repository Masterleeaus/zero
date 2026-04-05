<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TITAN OMNI — Pass 02 — Core tables
 *
 * Creates the six foundational Omni tables:
 *   omni_agents           — AI agent definitions per company
 *   omni_customers        — Omni-side channel identities (bridges to CRM Customer)
 *   omni_conversations    — Unified conversation threads (all channels)
 *   omni_messages         — Individual messages (immutable after creation)
 *   omni_channel_bridges  — Per-company channel credentials / webhook config
 *   omni_knowledge_articles — Company-scoped KB articles for RAG
 *
 * Donor source: CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED
 * Every table enforces company_id for tenant isolation.
 * omni_messages has no updated_at (append-only / immutable contract).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── omni_agents ──────────────────────────────────────────────────────
        if (! Schema::hasTable('omni_agents')) {
            Schema::create('omni_agents', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('role')->default('assistant');
                $table->string('model')->default('gpt-4o-mini');
                $table->string('avatar_url')->nullable();
                $table->text('instructions')->nullable();
                $table->text('system_prompt')->nullable();
                $table->string('tone')->default('professional');
                $table->string('language', 10)->default('en');
                $table->string('channel_scope')->default('all');
                $table->boolean('is_active')->default(true)->index();
                $table->boolean('is_favorite')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'is_active']);
            });
        }

        // ── omni_customers ───────────────────────────────────────────────────
        if (! Schema::hasTable('omni_customers')) {
            Schema::create('omni_customers', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('company_id')->index();
                // Nullable link to host CRM Customer — Omni never owns customer data
                $table->unsignedBigInteger('crm_customer_id')->nullable()->index();
                $table->string('name')->nullable();
                $table->string('email')->nullable()->index();
                $table->string('phone', 32)->nullable()->index();
                // Channel-specific sender identities keyed by channel type
                $table->json('channel_identities')->nullable();
                $table->string('external_ref')->nullable()->index();
                $table->json('tags')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'email']);
                $table->index(['company_id', 'phone']);
            });
        }

        // ── omni_conversations ───────────────────────────────────────────────
        if (! Schema::hasTable('omni_conversations')) {
            Schema::create('omni_conversations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->unsignedBigInteger('omni_customer_id')->nullable()->index();
                // Nullable host links (Omni reads only — never writes to host tables)
                $table->unsignedBigInteger('crm_customer_id')->nullable()->index();
                $table->unsignedBigInteger('linked_job_id')->nullable()->index();
                $table->unsignedBigInteger('linked_invoice_id')->nullable()->index();
                // Customer identity denorm for fast display (source of truth: omni_customers)
                $table->string('customer_name')->nullable();
                $table->string('customer_email')->nullable()->index();
                $table->string('session_id')->nullable()->index();
                $table->string('channel_type', 50)->default('webchat')->index();
                $table->string('channel_id')->nullable()->index();
                $table->string('external_conversation_id')->nullable()->index();
                $table->string('status', 50)->default('open')->index();
                // User assigned to handle this conversation
                $table->unsignedBigInteger('assigned_to')->nullable()->index();
                $table->boolean('is_pinned')->default(false);
                $table->timestamp('started_at')->nullable();
                // Set once on close — never overwritten (immutability contract)
                $table->timestamp('resolved_at')->nullable()->index();
                $table->timestamp('last_activity_at')->nullable()->index();
                $table->unsignedInteger('total_messages')->default(0);
                $table->json('tags')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'channel_type', 'status']);
                $table->index(['company_id', 'assigned_to', 'status']);
            });
        }

        // ── omni_messages ────────────────────────────────────────────────────
        // IMMUTABLE after creation. No updated_at. No soft-deletes.
        if (! Schema::hasTable('omni_messages')) {
            Schema::create('omni_messages', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('conversation_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                // direction: inbound | outbound
                $table->string('direction', 20)->default('inbound')->index();
                // content_type: text | image | audio | video | file | system_event
                $table->string('content_type', 50)->default('text')->index();
                $table->longText('content')->nullable();
                // sender_type: customer | agent | ai | system
                $table->string('sender_type', 50)->default('customer')->index();
                $table->unsignedBigInteger('sender_id')->nullable()->index();
                // Delivery evidence — set once, never overwritten
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->text('failure_reason')->nullable();
                // Media / voice
                $table->string('media_url')->nullable();
                $table->string('media_type')->nullable();
                $table->unsignedBigInteger('media_size_bytes')->nullable();
                $table->string('voice_file_url')->nullable();
                $table->unsignedInteger('voice_duration_seconds')->nullable();
                $table->longText('voice_transcript')->nullable();
                // Channel reference
                $table->string('external_message_id')->nullable()->index();
                $table->boolean('is_internal_note')->default(false);
                $table->json('metadata')->nullable();
                // created_at only — no updated_at (immutable)
                $table->timestamp('created_at')->nullable()->index();

                $table->index(['conversation_id', 'created_at']);
                $table->index(['company_id', 'direction', 'created_at']);
            });
        }

        // ── omni_channel_bridges ─────────────────────────────────────────────
        if (! Schema::hasTable('omni_channel_bridges')) {
            Schema::create('omni_channel_bridges', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->string('channel_type', 50)->index();
                $table->string('bridge_driver')->nullable();
                // Credentials stored encrypted at rest (enforced in application layer)
                $table->text('credentials')->nullable();
                $table->string('webhook_url')->nullable();
                $table->string('webhook_secret')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamp('verified_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'channel_type', 'is_active']);
            });
        }

        // ── omni_knowledge_articles ──────────────────────────────────────────
        if (! Schema::hasTable('omni_knowledge_articles')) {
            Schema::create('omni_knowledge_articles', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->string('title');
                $table->string('source_type', 50)->default('text')->index();
                $table->string('source_ref')->nullable();
                $table->longText('content')->nullable();
                $table->text('summary')->nullable();
                $table->string('embedding_model')->nullable();
                $table->string('status', 50)->default('active')->index();
                $table->json('tags')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'agent_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_knowledge_articles');
        Schema::dropIfExists('omni_channel_bridges');
        Schema::dropIfExists('omni_messages');
        Schema::dropIfExists('omni_conversations');
        Schema::dropIfExists('omni_customers');
        Schema::dropIfExists('omni_agents');
    }
};
