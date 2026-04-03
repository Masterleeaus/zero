<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('objectives', function (Blueprint $table) {
            if (!Schema::hasColumn('objectives', 'job_id')) {
                $table->unsignedBigInteger('job_id')->nullable()->after('project_id')->index();
            }
            if (!Schema::hasColumn('objectives', 'jobsite_id')) {
                $table->unsignedBigInteger('jobsite_id')->nullable()->after('job_id')->index();
            }
            if (!Schema::hasColumn('objectives', 'work_order_id')) {
                $table->unsignedBigInteger('work_order_id')->nullable()->after('jobsite_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('objectives', function (Blueprint $table) {
            if (Schema::hasColumn('objectives', 'work_order_id')) $table->dropColumn('work_order_id');
            if (Schema::hasColumn('objectives', 'jobsite_id')) $table->dropColumn('jobsite_id');
            if (Schema::hasColumn('objectives', 'job_id')) $table->dropColumn('job_id');
        });
    }
};
