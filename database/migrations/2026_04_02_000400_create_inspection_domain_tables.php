<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage B — Inspection Domain
 *
 * Creates the full inspection scheduling and execution framework:
 *   inspection_templates       — reusable inspection blueprints
 *   inspection_schedules       — recurring scheduling configuration
 *   inspection_instances       — individual inspection occurrences
 *   inspection_items           — line items within an instance
 *   inspection_responses       — technician responses to items
 *   inspection_attachments     — photos / files attached to an instance
 *
 * Sources: SiteInspection, ManagedPremises/Entities/PropertyInspection.php,
 *          FacilityManagement/Entities/Inspection.php.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Inspection Templates ──────────────────────────────────────────────
        Schema::create('inspection_templates', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->string('name');
            $table->string('inspection_type', 50)->nullable()
                ->comment('routine | safety | exit | entry | compliance | qa');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['company_id', 'inspection_type'], 'it_company_type');
        });

        // ── Template Items ────────────────────────────────────────────────────
        Schema::create('inspection_template_items', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_template_id')->index();

            $table->string('label');
            $table->string('response_type', 30)->default('pass_fail')
                ->comment('pass_fail | numeric | text | photo_required | signature_required | checkbox');
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('instructions')->nullable();

            $table->timestamps();

            $table->foreign('inspection_template_id')
                ->references('id')->on('inspection_templates')->onDelete('cascade');
        });

        // ── Inspection Schedules ──────────────────────────────────────────────
        Schema::create('inspection_schedules', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('inspection_template_id')->nullable()->index();

            // Scope: premises | building | unit
            $table->string('scope_type', 30)->nullable();
            $table->unsignedBigInteger('scope_id')->nullable();

            $table->string('name');
            $table->string('frequency', 30)->default('monthly')
                ->comment('daily | weekly | monthly | quarterly | annual');
            $table->unsignedSmallInteger('interval')->default(1)
                ->comment('e.g. every 2 weeks: frequency=weekly, interval=2');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->date('next_due_at')->nullable()->index();
            $table->date('last_completed_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['scope_type', 'scope_id'], 'is_scope');
            $table->index(['company_id', 'is_active'], 'is_company_active');
        });

        // ── Inspection Instances ──────────────────────────────────────────────
        Schema::create('inspection_instances', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('inspection_template_id')->nullable()->index();
            $table->unsignedBigInteger('inspection_schedule_id')->nullable()->index();

            // Scope: premises | building | unit | site_asset
            $table->string('scope_type', 30)->nullable();
            $table->unsignedBigInteger('scope_id')->nullable();

            // Optional service job link
            $table->unsignedBigInteger('service_job_id')->nullable()->index();

            $table->string('inspection_type', 50)->nullable();
            $table->string('title')->nullable();

            $table->string('status', 30)->default('scheduled')
                ->comment('scheduled | in_progress | completed | failed | cancelled');

            $table->unsignedBigInteger('inspector_id')->nullable()->index();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();

            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->unsignedTinyInteger('score')->nullable();
            $table->json('findings')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('followup_required')->default(false);
            $table->text('followup_notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['scope_type', 'scope_id'], 'ii_scope');
            $table->index(['company_id', 'status'], 'ii_company_status');
            $table->index(['company_id', 'scheduled_at'], 'ii_company_scheduled');
        });

        // ── Inspection Items (per-instance line items) ────────────────────────
        Schema::create('inspection_items', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_instance_id')->index();

            $table->unsignedBigInteger('template_item_id')->nullable()->index();
            $table->string('label');
            $table->string('response_type', 30)->default('pass_fail');
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('instructions')->nullable();

            $table->timestamps();

            $table->foreign('inspection_instance_id')
                ->references('id')->on('inspection_instances')->onDelete('cascade');
        });

        // ── Inspection Responses ──────────────────────────────────────────────
        Schema::create('inspection_responses', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_instance_id')->index();
            $table->unsignedBigInteger('inspection_item_id')->index();

            $table->string('result', 30)->nullable()
                ->comment('pass | fail | na | pending');
            $table->decimal('numeric_value', 12, 3)->nullable();
            $table->text('text_value')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('photo_required')->default(false);
            $table->boolean('signature_captured')->default(false);

            $table->unsignedBigInteger('responded_by')->nullable()->index();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('inspection_instance_id')
                ->references('id')->on('inspection_instances')->onDelete('cascade');
            $table->foreign('inspection_item_id')
                ->references('id')->on('inspection_items')->onDelete('cascade');
        });

        // ── Inspection Attachments ────────────────────────────────────────────
        Schema::create('inspection_attachments', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('inspection_instance_id')->index();
            $table->unsignedBigInteger('inspection_response_id')->nullable()->index();

            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('mime_type', 80)->nullable();
            $table->unsignedInteger('file_size')->nullable();

            $table->string('attachment_type', 30)->default('photo')
                ->comment('photo | signature | document | video');
            $table->text('caption')->nullable();

            $table->unsignedBigInteger('uploaded_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('inspection_instance_id')
                ->references('id')->on('inspection_instances')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_attachments');
        Schema::dropIfExists('inspection_responses');
        Schema::dropIfExists('inspection_items');
        Schema::dropIfExists('inspection_instances');
        Schema::dropIfExists('inspection_schedules');
        Schema::dropIfExists('inspection_template_items');
        Schema::dropIfExists('inspection_templates');
    }
};
