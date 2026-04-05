<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TITAN OMNI — Pass 02 — Campaign tables
 *
 * Creates:
 *   omni_campaigns             — Multi-channel broadcast campaigns
 *   omni_campaign_recipients   — Per-recipient delivery evidence (append-only status cols)
 *   omni_contact_lists         — Named recipient groups
 *   omni_contact_list_members  — Contact-to-list pivot
 *
 * Donor source: CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED
 * All tables enforce company_id.
 * omni_campaign_recipients delivery timestamps are set-once (immutability contract).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── omni_contact_lists ───────────────────────────────────────────────
        if (! Schema::hasTable('omni_contact_lists')) {
            Schema::create('omni_contact_lists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedInteger('member_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'name']);
            });
        }

        // ── omni_contact_list_members ────────────────────────────────────────
        if (! Schema::hasTable('omni_contact_list_members')) {
            Schema::create('omni_contact_list_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contact_list_id')->index();
                $table->unsignedBigInteger('omni_customer_id')->index();
                $table->timestamps();

                $table->unique(['contact_list_id', 'omni_customer_id']);
            });
        }

        // ── omni_campaigns ───────────────────────────────────────────────────
        if (! Schema::hasTable('omni_campaigns')) {
            Schema::create('omni_campaigns', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->unsignedBigInteger('contact_list_id')->nullable()->index();
                $table->string('name');
                $table->string('channel_type', 50)->index();
                $table->text('content')->nullable();
                $table->json('content_variables')->nullable();
                // status: draft | scheduled | running | completed | cancelled
                $table->string('status', 50)->default('draft')->index();
                $table->timestamp('scheduled_at')->nullable()->index();
                $table->timestamp('launched_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedInteger('total_recipients')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('delivered_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'channel_type', 'status']);
            });
        }

        // ── omni_campaign_recipients ─────────────────────────────────────────
        // Delivery timestamps are set-once and never overwritten (immutability contract).
        if (! Schema::hasTable('omni_campaign_recipients')) {
            Schema::create('omni_campaign_recipients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_id')->index();
                $table->unsignedBigInteger('omni_customer_id')->index();
                $table->string('channel_address')->nullable();
                // status: pending | sent | delivered | failed | opted_out
                $table->string('status', 50)->default('pending')->index();
                // Set-once delivery evidence — never overwritten
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->string('external_message_id')->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['campaign_id', 'status']);
                $table->unique(['campaign_id', 'omni_customer_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_campaign_recipients');
        Schema::dropIfExists('omni_campaigns');
        Schema::dropIfExists('omni_contact_list_members');
        Schema::dropIfExists('omni_contact_lists');
    }
};
