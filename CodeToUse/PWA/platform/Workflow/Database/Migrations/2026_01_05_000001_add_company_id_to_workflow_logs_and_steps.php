<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tenant safety: ensure workflow logs/steps are tenant-scoped.
        // Idempotent: safe to re-run.
        if (Schema::hasTable('workflow_logs') && !Schema::hasColumn('workflow_logs', 'company_id')) {
            Schema::table('workflow_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index()->after('id');
            });
        }

        if (Schema::hasTable('workflow_steps') && !Schema::hasColumn('workflow_steps', 'company_id')) {
            Schema::table('workflow_steps', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflow_logs') && Schema::hasColumn('workflow_logs', 'company_id')) {
            Schema::table('workflow_logs', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }

        if (Schema::hasTable('workflow_steps') && Schema::hasColumn('workflow_steps', 'company_id')) {
            Schema::table('workflow_steps', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
};
