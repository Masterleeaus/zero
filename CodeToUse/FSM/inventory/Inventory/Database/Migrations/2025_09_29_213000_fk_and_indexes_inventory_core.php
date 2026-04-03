<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // inventory_items helpful indexes
        if (Schema::hasTable('inventory_items')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_items','sku')) { $table->string('sku')->nullable()->index(); }
                if (!Schema::hasColumn('inventory_items','name')) { $table->string('name')->nullable()->index(); }
                if (!Schema::hasColumn('inventory_items','qty')) { $table->integer('qty')->default(0)->index(); }
                if (!Schema::hasColumn('inventory_items','category')) { $table->string('category')->nullable()->index(); }
            });
        }
        // stock_movements FKs + indexes
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (Schema::hasTable('inventory_items') && !self::hasForeign('stock_movements','stock_movements_item_id_foreign')) {
                    $table->foreign('item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
                }
                if (Schema::hasTable('warehouses') && !self::hasForeign('stock_movements','stock_movements_warehouse_id_foreign')) {
                    $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
                }
                $table->index(['item_id','warehouse_id']);
                $table->index(['type','created_at']);
            });
        }
        // stocktakes / lines FKs
        if (Schema::hasTable('stocktakes')) {
            Schema::table('stocktakes', function (Blueprint $table) {
                if (Schema::hasTable('warehouses') && !self::hasForeign('stocktakes','stocktakes_warehouse_id_foreign')) {
                    $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
                }
            });
        }
        if (Schema::hasTable('stocktake_lines')) {
            Schema::table('stocktake_lines', function (Blueprint $table) {
                if (Schema::hasTable('stocktakes') && !self::hasForeign('stocktake_lines','stocktake_lines_stocktake_id_foreign')) {
                    $table->foreign('stocktake_id')->references('id')->on('stocktakes')->cascadeOnDelete();
                }
                if (Schema::hasTable('inventory_items') && !self::hasForeign('stocktake_lines','stocktake_lines_item_id_foreign')) {
                    $table->foreign('item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
                }
                $table->index(['stocktake_id','item_id']);
            });
        }
    }

    public function down(): void
    {
        // Drop FKs safely
        if (Schema::hasTable('stocktake_lines')) {
            Schema::table('stocktake_lines', function (Blueprint $table) {
                self::dropForeignIfExists('stocktake_lines','stocktake_lines_stocktake_id_foreign');
                self::dropForeignIfExists('stocktake_lines','stocktake_lines_item_id_foreign');
            });
        }
        if (Schema::hasTable('stocktakes')) {
            Schema::table('stocktakes', function (Blueprint $table) {
                self::dropForeignIfExists('stocktakes','stocktakes_warehouse_id_foreign');
            });
        }
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                self::dropForeignIfExists('stock_movements','stock_movements_item_id_foreign');
                self::dropForeignIfExists('stock_movements','stock_movements_warehouse_id_foreign');
            });
        }
    }

    // Helpers for existence checks without DBAL
    public static function hasForeign(string $table, string $key): bool
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();
        foreach ($conn->listTableForeignKeys($table) as $fk) {
            if ($fk->getName() === $key) return true;
        }
        return false;
    }
    public static function dropForeignIfExists(string $table, string $key): void
    {
        try { Schema::table($table, function(Blueprint $t) use ($key) { $t->dropForeign($key); }); } catch (\Throwable $e) {}
    }
};
