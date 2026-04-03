<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            if (!Schema::hasColumn('timesheets','fsm_rate_per_hour')) $table->decimal('fsm_rate_per_hour', 10, 2)->nullable()->after('hours');
            if (!Schema::hasColumn('timesheets','fsm_overtime_multiplier')) $table->decimal('fsm_overtime_multiplier', 5, 2)->default(1.00)->after('fsm_rate_per_hour');
            if (!Schema::hasColumn('timesheets','fsm_cost_total')) $table->decimal('fsm_cost_total', 12, 2)->nullable()->after('fsm_overtime_multiplier');
            if (!Schema::hasColumn('timesheets','work_order_id')) $table->unsignedBigInteger('work_order_id')->nullable()->index()->after('user_id');
            if (!Schema::hasColumn('timesheets','company_id')) $table->unsignedBigInteger('company_id')->nullable()->index()->after('id');
        });
    }
    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            if (Schema::hasColumn('timesheets','fsm_rate_per_hour')) $table->dropColumn('fsm_rate_per_hour');
            if (Schema::hasColumn('timesheets','fsm_overtime_multiplier')) $table->dropColumn('fsm_overtime_multiplier');
            if (Schema::hasColumn('timesheets','fsm_cost_total')) $table->dropColumn('fsm_cost_total');
            if (Schema::hasColumn('timesheets','work_order_id')) $table->dropColumn('work_order_id');
            if (Schema::hasColumn('timesheets','company_id')) $table->dropColumn('company_id');
        });
    }
};
