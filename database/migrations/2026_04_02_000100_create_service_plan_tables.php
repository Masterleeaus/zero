<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage D — Agreement ↔ ServicePlan ↔ ServiceJob Triangle
 *
 * ServicePlan defines the schedule for an Agreement.
 * ServicePlanVisit represents individual scheduled occurrences which
 * are linked to an executing ServiceJob when dispatched.
 *
 * Hierarchy: Agreement → ServicePlan → ServicePlanVisit → ServiceJob
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Service Plans (schedule definition for an Agreement) ──────────────
        Schema::create('service_plans', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('agreement_id')->index();
            $table->unsignedBigInteger('premises_id')->nullable()->index();

            $table->string('title');
            $table->string('frequency', 30)->default('monthly')
                ->comment('daily | weekly | fortnightly | monthly | quarterly | annual | custom');
            $table->unsignedSmallInteger('visits_per_cycle')->default(1);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('active')
                ->comment('active | paused | completed | cancelled');
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('agreement_id')
                ->references('id')->on('service_agreements')
                ->onDelete('cascade');

            $table->index(['company_id', 'status'], 'sp_company_status');
            $table->index(['company_id', 'agreement_id'], 'sp_company_agreement');
        });

        // ── Service Plan Visits (individual scheduled occurrences) ────────────
        Schema::create('service_plan_visits', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('service_plan_id')->index();
            $table->unsignedBigInteger('service_job_id')->nullable()->index()
                ->comment('Populated when this visit is dispatched as a job');

            $table->date('scheduled_date');
            $table->string('status', 30)->default('pending')
                ->comment('pending | scheduled | completed | skipped | cancelled');
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('service_plan_id')
                ->references('id')->on('service_plans')
                ->onDelete('cascade');

            $table->foreign('service_job_id')
                ->references('id')->on('service_jobs')
                ->onDelete('set null');

            $table->index(['company_id', 'status'], 'spv_company_status');
            $table->index(['company_id', 'service_plan_id'], 'spv_company_plan');
            $table->index(['company_id', 'scheduled_date'], 'spv_company_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plan_visits');
        Schema::dropIfExists('service_plans');
    }
};
