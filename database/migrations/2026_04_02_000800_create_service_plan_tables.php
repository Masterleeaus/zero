<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage E — Service Plan Domain
 *
 * Recurring service configuration separate from agreements.
 * Agreement → defines entitlement
 * ServicePlan → defines schedule / visits
 * ServiceJob → executes work
 *
 *   service_plans           — plan configuration per premises/customer
 *   service_plan_visits     — individual planned visits (can generate a ServiceJob)
 *   service_plan_checklists — checklist templates injected into each visit
 *
 * Sources: ManagedPremises/Entities/PropertyServicePlan.php,
 *          ManagedPremises/Entities/PropertyVisit.php.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Service Plans ─────────────────────────────────────────────────────
        Schema::create('service_plans', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('premises_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();

            // Optional agreement linkage (Agreement defines entitlement)
            $table->unsignedBigInteger('agreement_id')->nullable()->index();

            $table->string('name');
            $table->string('service_type', 80)->nullable()
                ->comment('e.g. cleaning | inspection | maintenance | pest_control');

            // Recurrence
            $table->string('frequency', 30)->default('monthly')
                ->comment('daily | weekly | fortnightly | monthly | quarterly | annual');
            $table->unsignedSmallInteger('interval')->default(1);
            $table->string('rrule')->nullable()
                ->comment('RFC5545 RRULE for complex schedules');
            $table->json('preferred_days')->nullable();
            $table->json('preferred_times')->nullable();

            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->date('next_visit_due')->nullable()->index();
            $table->date('last_visit_completed')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['company_id', 'is_active'], 'sp_company_active');
        });

        // ── Service Plan Visits ───────────────────────────────────────────────
        Schema::create('service_plan_visits', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('service_plan_id')->index();

            // Generated ServiceJob (populated when the visit converts to a job)
            $table->unsignedBigInteger('service_job_id')->nullable()->index();

            $table->string('visit_type', 60)->nullable()
                ->comment('service | inspection | maintenance | key_handover');
            $table->dateTime('scheduled_for')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();

            $table->string('status', 30)->default('scheduled')
                ->comment('scheduled | confirmed | in_progress | completed | cancelled | no_access');

            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('service_plan_id')
                ->references('id')->on('service_plans')->onDelete('cascade');
            $table->index(['company_id', 'status'], 'spv_company_status');
            $table->index(['company_id', 'scheduled_for'], 'spv_company_scheduled');
        });

        // ── Service Plan Checklists ───────────────────────────────────────────
        Schema::create('service_plan_checklists', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_plan_id')->index();

            // Checklist template to inject into each generated visit / job
            $table->unsignedBigInteger('checklist_template_id')->nullable()->index();

            // Optional inspection template to inject
            $table->unsignedBigInteger('inspection_template_id')->nullable()->index();

            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('service_plan_id')
                ->references('id')->on('service_plans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plan_checklists');
        Schema::dropIfExists('service_plan_visits');
        Schema::dropIfExists('service_plans');
    }
};
