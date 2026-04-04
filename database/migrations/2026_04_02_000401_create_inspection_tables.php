<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage J — Inspection Engine tables.
 *
 * inspection_instances  — an executed inspection linked to a job/premises/unit.
 * checklist_runs        — a checklist execution linked to a job or inspection.
 *
 * Inspection types: routine | compliance | handover | safety | quality
 * Status values:    pending | in_progress | completed | failed | cancelled
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Inspection Instances ──────────────────────────────────────────────
        Schema::create('inspection_instances', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            // Contextual links
            $table->unsignedBigInteger('service_job_id')->nullable()->index();
            $table->unsignedBigInteger('premises_id')->nullable()->index();
            $table->unsignedBigInteger('building_id')->nullable()->index();
            $table->unsignedBigInteger('floor_id')->nullable()->index();
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->unsignedBigInteger('site_asset_id')->nullable()->index();

            $table->string('inspection_type', 40)->default('routine')
                ->comment('routine | compliance | handover | safety | quality');
            $table->string('status', 30)->default('pending')
                ->comment('pending | in_progress | completed | failed | cancelled');

            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->text('fail_reason')->nullable();

            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('service_job_id')
                ->references('id')->on('service_jobs')
                ->onDelete('set null');

            $table->index(['company_id', 'status'], 'ii_company_status');
            $table->index(['company_id', 'premises_id'], 'ii_company_premises');
            $table->index(['company_id', 'service_job_id'], 'ii_company_job');
        });

        // ── Checklist Runs ────────────────────────────────────────────────────
        Schema::create('checklist_runs', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            // Polymorphic owner: linked to a job, inspection, or premises
            $table->unsignedBigInteger('service_job_id')->nullable()->index();
            $table->unsignedBigInteger('inspection_instance_id')->nullable()->index();
            $table->unsignedBigInteger('premises_id')->nullable()->index();

            // Source checklist template
            $table->unsignedBigInteger('checklist_id')->nullable()->index();

            $table->string('title')->nullable();
            $table->string('status', 30)->default('pending')
                ->comment('pending | in_progress | completed | failed');

            $table->unsignedSmallInteger('items_total')->default(0);
            $table->unsignedSmallInteger('items_completed')->default(0);
            $table->unsignedSmallInteger('items_failed')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('service_job_id')
                ->references('id')->on('service_jobs')
                ->onDelete('set null');

            $table->foreign('inspection_instance_id')
                ->references('id')->on('inspection_instances')
                ->onDelete('set null');

            $table->index(['company_id', 'status'], 'cr_company_status');
            $table->index(['company_id', 'service_job_id'], 'cr_company_job');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_runs');
        Schema::dropIfExists('inspection_instances');
    }
};
