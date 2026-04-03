<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('global_settings')) return;

        $row = DB::table('global_settings')->first();
        if (!$row) {
            DB::table('global_settings')->insert([
                'frontend_disable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (!isset($row->frontend_disable)) {
            DB::table('global_settings')->update([
                'frontend_disable' => 0,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // No-op: never delete global settings data in down()
    }
};
