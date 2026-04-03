<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FSM Modules 9+10 — Repair Domain + Repair Template Engine
 *
 * Creates all repair domain tables:
 *   repair_templates, repair_template_steps, repair_template_parts,
 *   repair_template_checklists, repair_orders, repair_diagnoses,
 *   repair_tasks, repair_actions, repair_part_usages, repair_checklists,
 *   repair_resolutions
 */
return new class extends Migration {
    public function up(): void
    {
        // ── repair_templates ──────────────────────────────────────────────────
        Schema::create('repair_templates', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('template_category')->nullable()
                ->comment('equipment_type|fault_type|manufacturer|service_category|agreement_type|warranty_type');
            $table->string('equipment_type')->nullable();
            $table->string('fault_type')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('service_category')->nullable();
            $table->integer('estimated_duration')->nullable()->comment('minutes');
            $table->text('safety_notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // ── repair_template_steps ─────────────────────────────────────────────
        Schema::create('repair_template_steps', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreignId('repair_template_id')
                ->constrained('repair_templates')
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('step_type')->default('task');
            $table->integer('sequence')->default(0);
            $table->integer('estimated_duration')->nullable()->comment('minutes');
            $table->boolean('requires_parts')->default(false);
            $table->boolean('safety_flag')->default(false);
            $table->timestamps();
        });

        // ── repair_template_parts ─────────────────────────────────────────────
        Schema::create('repair_template_parts', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreignId('repair_template_id')
                ->constrained('repair_templates')
                ->cascadeOnDelete();
            $table->string('part_name');
            $table->string('part_sku')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->boolean('optional')->default(false);
            $table->timestamps();
        });

        // ── repair_template_checklists ────────────────────────────────────────
        Schema::create('repair_template_checklists', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreignId('repair_template_id')
                ->constrained('repair_templates')
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('checklist_type')->nullable();
            $table->json('items')->nullable();
            $table->timestamps();
        });

        // ── repair_orders ─────────────────────────────────────────────────────
        Schema::create('repair_orders', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('repair_number')->unique();
            $table->foreignId('equipment_id')
                ->nullable()
                ->constrained('equipment')
                ->nullOnDelete();
            $table->foreignId('installed_equipment_id')
                ->nullable()
                ->constrained('installed_equipment')
                ->nullOnDelete();
            $table->foreignId('site_asset_id')
                ->nullable()
                ->constrained('site_assets')
                ->nullOnDelete();
            $table->foreignId('premises_id')
                ->nullable()
                ->constrained('premises')
                ->nullOnDelete();
            $table->foreignId('service_job_id')
                ->nullable()
                ->constrained('service_jobs')
                ->nullOnDelete();
            $table->foreignId('warranty_claim_id')
                ->nullable()
                ->constrained('warranty_claims')
                ->nullOnDelete();
            $table->unsignedBigInteger('agreement_id')->nullable()->index();
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();
            $table->foreignId('assigned_team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();
            $table->foreignId('assigned_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->nullableForeignId('repair_template_id')
                ->constrained('repair_templates')
                ->nullOnDelete();
            $table->string('priority')->default('normal');
            $table->string('severity')->default('medium');
            $table->string('fault_category')->nullable();
            $table->string('repair_type')->nullable();
            $table->string('repair_status')->default('draft');
            $table->boolean('requires_parts')->default(false);
            $table->boolean('requires_followup')->default(false);
            $table->boolean('requires_quote')->default(false);
            $table->boolean('requires_return_visit')->default(false);
            $table->text('diagnosis_summary')->nullable();
            $table->text('resolution_summary')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── repair_diagnoses ──────────────────────────────────────────────────
        Schema::create('repair_diagnoses', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreignId('repair_order_id')
                ->constrained('repair_orders')
                ->cascadeOnDelete();
            $table->text('symptom');
            $table->text('cause')->nullable();
            $table->text('recommended_action')->nullable();
            $table->boolean('safety_flag')->default(false);
            $table->boolean('requires_specialist')->default(false);
            $table->boolean('requires_parts')->default(false);
            $table->boolean('requires_quote')->default(false);
            $table->integer('estimated_duration')->nullable()->comment('minutes');
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── repair_tasks ──────────────────────────────────────────────────────
        Schema::create('repair_tasks', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreignId('repair_order_id')
                ->constrained('repair_orders')
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->integer('sequence')->default(0);
            $table->foreignId('assigned_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── repair_actions ────────────────────────────────────────────────────
        Schema::create('repair_actions', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreignId('repair_order_id')
                ->constrained('repair_orders')
                ->cascadeOnDelete();
            $table->foreignId('repair_task_id')
                ->nullable()
                ->constrained('repair_tasks')
                ->nullOnDelete();
            $table->string('action_type');
            $table->text('description');
            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('performed_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();
        });

        // ── repair_part_usages ────────────────────────────────────────────────
        Schema::create('repair_part_usages', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreignId('repair_order_id')
                ->constrained('repair_orders')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('part_id')->nullable();
            $table->string('part_name');
            $table->string('part_sku')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('stock_location')->nullable();
            $table->string('movement_reference')->nullable();
            $table->boolean('reserved')->default(false);
            $table->boolean('consumed')->default(false);
            $table->string('supplier_reference')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── repair_checklists ─────────────────────────────────────────────────
        Schema::create('repair_checklists', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreignId('repair_order_id')
                ->constrained('repair_orders')
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('checklist_type')->nullable();
            $table->string('status')->default('pending');
            $table->integer('items_total')->default(0);
            $table->integer('items_completed')->default(0);
            $table->integer('items_failed')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── repair_resolutions ────────────────────────────────────────────────
        Schema::create('repair_resolutions', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreignId('repair_order_id')
                ->constrained('repair_orders')
                ->cascadeOnDelete();
            $table->string('resolution_type');
            $table->text('resolution_notes');
            $table->text('root_cause')->nullable();
            $table->text('preventive_action')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->text('follow_up_notes')->nullable();
            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_resolutions');
        Schema::dropIfExists('repair_checklists');
        Schema::dropIfExists('repair_part_usages');
        Schema::dropIfExists('repair_actions');
        Schema::dropIfExists('repair_tasks');
        Schema::dropIfExists('repair_diagnoses');
        Schema::dropIfExists('repair_orders');
        Schema::dropIfExists('repair_template_checklists');
        Schema::dropIfExists('repair_template_parts');
        Schema::dropIfExists('repair_template_steps');
        Schema::dropIfExists('repair_templates');
    }
};
