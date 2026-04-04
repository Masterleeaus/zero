<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── mesh_nodes ──────────────────────────────────────────────────────
        Schema::create('mesh_nodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('node_id')->unique();
            $table->string('node_name');
            $table->string('node_url');
            $table->enum('trust_level', ['observer', 'standard', 'trusted', 'partner'])->default('observer');
            $table->text('public_key');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_handshake_at')->nullable();
            $table->string('capabilities_hash', 64)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── mesh_capability_exports ─────────────────────────────────────────
        Schema::create('mesh_capability_exports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->enum('capability_type', ['skill', 'certification', 'job_type', 'service_area', 'equipment_type']);
            $table->string('capability_value');
            $table->unsignedInteger('available_count')->default(0);
            $table->json('geographic_scope')->nullable();
            $table->boolean('is_exported')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'capability_type', 'capability_value']);
        });

        // ── mesh_dispatch_requests ──────────────────────────────────────────
        Schema::create('mesh_dispatch_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requesting_company_id')->index();
            $table->unsignedBigInteger('fulfilling_company_id')->nullable()->index();
            $table->unsignedBigInteger('original_job_id')->nullable();
            $table->json('required_capabilities');
            $table->json('location')->nullable();
            $table->enum('urgency', ['low', 'normal', 'high', 'emergency'])->default('normal');
            $table->enum('status', ['open', 'offered', 'accepted', 'executing', 'completed', 'rejected', 'expired'])->default('open');
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('mesh_job_reference', 64)->nullable();
            $table->string('evidence_hash', 64)->nullable();
            $table->decimal('commission_rate', 5, 4)->default(0.0500);
            $table->timestamps();
            $table->softDeletes();
        });

        // ── mesh_trust_events ───────────────────────────────────────────────
        Schema::create('mesh_trust_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('node_id')->index();
            $table->enum('event_type', [
                'handshake',
                'job_completed',
                'dispute_raised',
                'trust_upgraded',
                'trust_downgraded',
                'node_suspended',
            ]);
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            // append-only — no updated_at
            $table->timestamp('created_at')->nullable();
        });

        // ── mesh_settlements ────────────────────────────────────────────────
        Schema::create('mesh_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mesh_dispatch_request_id')->index();
            $table->unsignedBigInteger('requesting_company_id')->index();
            $table->unsignedBigInteger('fulfilling_company_id')->index();
            $table->decimal('amount', 12, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('net_amount', 12, 2);
            $table->string('currency', 3)->default('AUD');
            $table->enum('status', ['pending', 'invoiced', 'paid', 'disputed'])->default('pending');
            $table->string('invoice_reference')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesh_settlements');
        Schema::dropIfExists('mesh_trust_events');
        Schema::dropIfExists('mesh_dispatch_requests');
        Schema::dropIfExists('mesh_capability_exports');
        Schema::dropIfExists('mesh_nodes');
    }
};
