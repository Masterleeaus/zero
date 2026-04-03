<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('check_ins') && !Schema::hasColumn('check_ins', 'company_id')) {
            Schema::table('check_ins', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('goal_types') && !Schema::hasColumn('goal_types', 'company_id')) {
            Schema::table('goal_types', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('job_performance_snapshots') && !Schema::hasColumn('job_performance_snapshots', 'company_id')) {
            Schema::table('job_performance_snapshots', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('job_quality_metrics') && !Schema::hasColumn('job_quality_metrics', 'company_id')) {
            Schema::table('job_quality_metrics', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('job_safety_metrics') && !Schema::hasColumn('job_safety_metrics', 'company_id')) {
            Schema::table('job_safety_metrics', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('key_results') && !Schema::hasColumn('key_results', 'company_id')) {
            Schema::table('key_results', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('key_results_metrics') && !Schema::hasColumn('key_results_metrics', 'company_id')) {
            Schema::table('key_results_metrics', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('objective_owners') && !Schema::hasColumn('objective_owners', 'company_id')) {
            Schema::table('objective_owners', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('objective_progress_statuses') && !Schema::hasColumn('objective_progress_statuses', 'company_id')) {
            Schema::table('objective_progress_statuses', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('objectives') && !Schema::hasColumn('objectives', 'company_id')) {
            Schema::table('objectives', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('performance_global_settings') && !Schema::hasColumn('performance_global_settings', 'company_id')) {
            Schema::table('performance_global_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('performance_meeting_actions') && !Schema::hasColumn('performance_meeting_actions', 'company_id')) {
            Schema::table('performance_meeting_actions', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('performance_meeting_agenda') && !Schema::hasColumn('performance_meeting_agenda', 'company_id')) {
            Schema::table('performance_meeting_agenda', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('performance_meetings') && !Schema::hasColumn('performance_meetings', 'company_id')) {
            Schema::table('performance_meetings', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('performance_settings') && !Schema::hasColumn('performance_settings', 'company_id')) {
            Schema::table('performance_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        // intentionally non-destructive
    }
};
