<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('timesheets')) {
            return;
        }

        Schema::table('timesheets', function (Blueprint $table) {
            // Guard: only add if not already present
            try { $table->index(['company_id', 'date'], 'timesheets_company_date_idx'); } catch (\Throwable $e) {}
            try { $table->index(['user_id', 'date'], 'timesheets_user_date_idx'); } catch (\Throwable $e) {}
            try { $table->index(['work_order_id', 'date'], 'timesheets_workorder_date_idx'); } catch (\Throwable $e) {}
            try { $table->index(['project_id', 'date'], 'timesheets_project_date_idx'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('timesheets')) {
            return;
        }

        Schema::table('timesheets', function (Blueprint $table) {
            try { $table->dropIndex('timesheets_company_date_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('timesheets_user_date_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('timesheets_workorder_date_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('timesheets_project_date_idx'); } catch (\Throwable $e) {}
        });
    }
};
