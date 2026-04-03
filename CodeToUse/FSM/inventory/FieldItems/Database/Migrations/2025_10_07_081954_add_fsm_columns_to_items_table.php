<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function suppliersIdIsBigint(): bool
    {
        // Detect suppliers.id type without requiring doctrine/dbal
        $row = DB::selectOne("
            SELECT DATA_TYPE, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'suppliers'
              AND COLUMN_NAME = 'id'
            LIMIT 1
        ");

        if (!$row) {
            // Default to BIGINT if suppliers table not present (or unknown)
            return true;
        }

        // DATA_TYPE = 'bigint' or 'int', COLUMN_TYPE includes 'unsigned'
        return strtolower($row->DATA_TYPE ?? '') === 'bigint';
    }

    private function hasForeignKey(string $table, string $constraint): bool
    {
        $exists = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            LIMIT 1
        ", [$table, $constraint]);

        return (bool) $exists;
    }

    public function up(): void
    {
        // 1) Add new scalar columns if they don't exist
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'fsm_cost')) {
                $table->decimal('fsm_cost', 12, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('items', 'fsm_markup_percent')) {
                $table->decimal('fsm_markup_percent', 5, 2)->nullable()->after('fsm_cost');
            }
            if (!Schema::hasColumn('items', 'fsm_unit')) {
                $table->string('fsm_unit', 191)->nullable()->after('fsm_markup_percent');
            }
        });

        // 2) Add supplier FK column with a type that matches suppliers.id
        $isBig = $this->suppliersIdIsBigint();

        Schema::table('items', function (Blueprint $table) use ($isBig) {
            if (!Schema::hasColumn('items', 'fsm_default_supplier_id')) {
                if ($isBig) {
                    $table->unsignedBigInteger('fsm_default_supplier_id')->nullable()->after('fsm_unit');
                } else {
                    $table->unsignedInteger('fsm_default_supplier_id')->nullable()->after('fsm_unit');
                }
            }
        });

        // 3) Align existing type if it was created with the wrong width earlier
        //    (No doctrine/dbal, so use raw SQL to MODIFY if needed.)
        $col = DB::selectOne("
            SELECT DATA_TYPE, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'items'
              AND COLUMN_NAME = 'fsm_default_supplier_id'
            LIMIT 1
        ");

        if ($col) {
            $isColBig = strtolower($col->DATA_TYPE ?? '') === 'bigint';
            if ($isBig && !$isColBig) {
                // make it BIGINT UNSIGNED NULL
                DB::statement("ALTER TABLE `items` MODIFY `fsm_default_supplier_id` BIGINT UNSIGNED NULL");
            } elseif (!$isBig && $isColBig) {
                // make it INT UNSIGNED NULL
                DB::statement("ALTER TABLE `items` MODIFY `fsm_default_supplier_id` INT UNSIGNED NULL");
            }
        }

        // 4) Add the foreign key if missing
        if (!$this->hasForeignKey('items', 'items_fsm_default_supplier_id_foreign')) {
            Schema::table('items', function (Blueprint $table) {
                // ensure suppliers table exists before adding FK
                if (Schema::hasTable('suppliers')) {
                    $table->foreign('fsm_default_supplier_id', 'items_fsm_default_supplier_id_foreign')
                          ->references('id')->on('suppliers')
                          ->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        // Drop FK if present
        if ($this->hasForeignKey('items', 'items_fsm_default_supplier_id_foreign')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropForeign('items_fsm_default_supplier_id_foreign');
            });
        }

        // Drop columns if present
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'fsm_default_supplier_id')) {
                $table->dropColumn('fsm_default_supplier_id');
            }
            if (Schema::hasColumn('items', 'fsm_unit')) {
                $table->dropColumn('fsm_unit');
            }
            if (Schema::hasColumn('items', 'fsm_markup_percent')) {
                $table->dropColumn('fsm_markup_percent');
            }
            if (Schema::hasColumn('items', 'fsm_cost')) {
                $table->dropColumn('fsm_cost');
            }
        });
    }
};
