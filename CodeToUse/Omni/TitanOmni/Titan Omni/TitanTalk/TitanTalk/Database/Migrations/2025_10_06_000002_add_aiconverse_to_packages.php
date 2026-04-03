<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('packages') || !Schema::hasColumn('packages', 'module_in_package')) return;
        DB::statement("
            UPDATE packages
            SET module_in_package = JSON_SET(
                COALESCE(NULLIF(module_in_package, ''), JSON_OBJECT()),
                '$.aiconverse', TRUE
            )
        ");
    }
    public function down(): void
    {
        if (!Schema::hasTable('packages') || !Schema::hasColumn('packages', 'module_in_package')) return;
        DB::statement("
            UPDATE packages
            SET module_in_package = IFNULL(
                JSON_REMOVE(CAST(module_in_package AS JSON), '$.aiconverse'),
                JSON_OBJECT()
            )
        ");
    }
};
