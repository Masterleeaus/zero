<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage E — Service Plan Domain (extension pass)
 *
 * Extends the service_plans and service_plan_visits tables created in
 * 000100_create_service_plan_tables.php with richer scheduling fields.
 * Also creates service_plan_checklists which is new.
 *
 * Sources: ManagedPremises/Entities/PropertyServicePlan.php,
 *          ManagedPremises/Entities/PropertyVisit.php.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Extend service_plans with richer scheduling fields ────────────────
        Schema::table('service_plans', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_plans', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->index()->after('premises_id');
            }
            if (! Schema::hasColumn('service_plans', 'name')) {
                $table->string('name')->nullable()->after('customer_id');
            }
            if (! Schema::hasColumn('service_plans', 'service_type')) {
                $table->string('service_type', 80)->nullable()
                    ->comment('cleaning | inspection | maintenance | pest_control')
                    ->after('name');
            }
            if (! Schema::hasColumn('service_plans', 'interval')) {
                $table->unsignedSmallInteger('interval')->default(1)->after('frequency');
            }
            if (! Schema::hasColumn('service_plans', 'rrule')) {
                $table->string('rrule')->nullable()
                    ->comment('RFC5545 RRULE for complex schedules')->after('interval');
            }
            if (! Schema::hasColumn('service_plans', 'preferred_days')) {
                $table->json('preferred_days')->nullable()->after('rrule');
            }
            if (! Schema::hasColumn('service_plans', 'preferred_times')) {
                $table->json('preferred_times')->nullable()->after('preferred_days');
            }
            if (! Schema::hasColumn('service_plans', 'starts_on')) {
                $table->date('starts_on')->nullable()->after('preferred_times');
            }
            if (! Schema::hasColumn('service_plans', 'ends_on')) {
                $table->date('ends_on')->nullable()->after('starts_on');
            }
            if (! Schema::hasColumn('service_plans', 'next_visit_due')) {
                $table->date('next_visit_due')->nullable()->index()->after('ends_on');
            }
            if (! Schema::hasColumn('service_plans', 'last_visit_completed')) {
                $table->date('last_visit_completed')->nullable()->after('next_visit_due');
            }
            if (! Schema::hasColumn('service_plans', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('last_visit_completed');
            }
        });

        // ── Extend service_plan_visits with richer visit fields ───────────────
        Schema::table('service_plan_visits', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_plan_visits', 'visit_type')) {
                $table->string('visit_type', 60)->nullable()
                    ->comment('service | inspection | maintenance | key_handover')
                    ->after('service_job_id');
            }
            if (! Schema::hasColumn('service_plan_visits', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->index()->after('visit_type');
            }
            if (! Schema::hasColumn('service_plan_visits', 'scheduled_for')) {
                $table->dateTime('scheduled_for')->nullable()->after('assigned_to');
            }
        });

        // ── Service Plan Checklists (new table) ───────────────────────────────
        if (! Schema::hasTable('service_plan_checklists')) {
            Schema::create('service_plan_checklists', static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('service_plan_id')->index();
                $table->unsignedBigInteger('checklist_template_id')->nullable()->index();
                $table->unsignedBigInteger('inspection_template_id')->nullable()->index();
                $table->string('label')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('service_plan_id')
                    ->references('id')->on('service_plans')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plan_checklists');

        Schema::table('service_plan_visits', static function (Blueprint $table) {
            foreach (['visit_type', 'assigned_to', 'scheduled_for'] as $col) {
                if (Schema::hasColumn('service_plan_visits', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('service_plans', static function (Blueprint $table) {
            $cols = [
                'customer_id', 'name', 'service_type', 'interval', 'rrule',
                'preferred_days', 'preferred_times', 'starts_on', 'ends_on',
                'next_visit_due', 'last_visit_completed', 'is_active',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('service_plans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
