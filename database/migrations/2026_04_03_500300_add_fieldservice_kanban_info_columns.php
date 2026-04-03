<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — fieldservice_kanban_info (Module 23)
 *
 * Extends:
 *   - service_jobs  : kanban state, SLA, readiness score
 *   - job_stages    : kanban display metadata (badge, badge color)
 *
 * Creates:
 *   - fsm_job_status_meta    : per-job computed status overlay
 *   - fsm_job_blockers       : blocking reasons attached to a job
 *   - fsm_job_priority_scores: dispatch/urgency scoring
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Extend service_jobs ──────────────────────────────────────────────
        Schema::table('service_jobs', function (Blueprint $table): void {
            // Kanban state (normal | blocked | ready_for_next_stage)
            $table->string('kanban_state')->default('normal')->after('stage_id');
            $table->string('kanban_state_label')->nullable()->after('kanban_state');

            // SLA / deadline awareness
            $table->timestamp('sla_deadline')->nullable()->after('kanban_state_label');
            $table->boolean('sla_breached')->default(false)->after('sla_deadline');

            // Computed readiness score (0–100, written by KanbanStatusService)
            $table->unsignedSmallInteger('readiness_score')->default(0)->after('sla_breached');
        });

        // ── Extend job_stages ────────────────────────────────────────────────
        Schema::table('job_stages', function (Blueprint $table): void {
            $table->string('display_badge')->nullable()->after('color');
            $table->string('badge_color')->nullable()->after('display_badge');
            $table->boolean('kanban_fold')->default(false)->after('badge_color');
        });

        // ── fsm_job_status_meta ──────────────────────────────────────────────
        Schema::create('fsm_job_status_meta', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('service_job_id')->index();

            // Readiness flags
            $table->boolean('is_ready_to_start')->default(false);
            $table->boolean('is_waiting_parts')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_overdue')->default(false);
            $table->boolean('requires_followup')->default(false);
            $table->boolean('customer_action_pending')->default(false);

            // Dispatch enrichment
            $table->unsignedTinyInteger('priority_score')->default(0);
            $table->boolean('delay_risk')->default(false);
            $table->boolean('travel_conflict_flag')->default(false);
            $table->boolean('crew_skill_mismatch')->default(false);
            $table->boolean('equipment_missing')->default(false);
            $table->boolean('contract_violation')->default(false);

            // Equipment / agreement awareness
            $table->boolean('equipment_warranty_expired')->default(false);
            $table->boolean('agreement_expired')->default(false);
            $table->boolean('vip_client_flag')->default(false);

            // Technician readiness
            $table->boolean('technician_prep_done')->default(false);

            $table->timestamp('refreshed_at')->nullable();
            $table->timestamps();

            $table->foreign('service_job_id')
                ->references('id')
                ->on('service_jobs')
                ->cascadeOnDelete();
        });

        // ── fsm_job_blockers ─────────────────────────────────────────────────
        Schema::create('fsm_job_blockers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('service_job_id')->index();

            $table->string('blocker_type');   // parts_missing | agreement_expired | equipment_fault | customer_hold | etc.
            $table->string('blocker_label');
            $table->text('details')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();

            $table->timestamps();

            $table->foreign('service_job_id')
                ->references('id')
                ->on('service_jobs')
                ->cascadeOnDelete();
        });

        // ── fsm_job_priority_scores ──────────────────────────────────────────
        Schema::create('fsm_job_priority_scores', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('service_job_id')->index();

            // Composite dispatch score components (each 0–100)
            $table->unsignedTinyInteger('urgency_score')->default(0);
            $table->unsignedTinyInteger('sla_score')->default(0);
            $table->unsignedTinyInteger('client_tier_score')->default(0);
            $table->unsignedTinyInteger('agreement_score')->default(0);
            $table->unsignedTinyInteger('equipment_score')->default(0);

            // Final weighted score (0–100)
            $table->unsignedTinyInteger('total_score')->default(0);

            // Snapshot metadata
            $table->json('score_breakdown')->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();

            $table->foreign('service_job_id')
                ->references('id')
                ->on('service_jobs')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fsm_job_priority_scores');
        Schema::dropIfExists('fsm_job_blockers');
        Schema::dropIfExists('fsm_job_status_meta');

        Schema::table('job_stages', function (Blueprint $table): void {
            $table->dropColumn(['display_badge', 'badge_color', 'kanban_fold']);
        });

        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->dropColumn([
                'kanban_state',
                'kanban_state_label',
                'sla_deadline',
                'sla_breached',
                'readiness_score',
            ]);
        });
    }
};
