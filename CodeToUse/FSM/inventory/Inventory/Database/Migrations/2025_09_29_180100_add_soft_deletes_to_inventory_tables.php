<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        foreach (['inventory_items','warehouses','stock_movements','stocktakes','stocktake_lines'] as $t) {
            if (Schema::hasTable($t) && !Schema::hasColumn($t,'deleted_at')) {
                Schema::table($t, function(Blueprint $table){ $table->softDeletes(); });
            }
        }
    }
    public function down(): void {
        foreach (['inventory_items','warehouses','stock_movements','stocktakes','stocktake_lines'] as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t,'deleted_at')) {
                Schema::table($t, function(Blueprint $table){ $table->dropSoftDeletes(); });
            }
        }
    }
};
