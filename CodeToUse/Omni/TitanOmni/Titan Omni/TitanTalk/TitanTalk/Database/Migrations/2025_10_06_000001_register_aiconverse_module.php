<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('modules')) return;
        $exists = DB::table('modules')->where('module_name', 'aiconverse')->exists();
        if (!$exists) {
            DB::table('modules')->insert([
                'module_name'   => 'aiconverse',
                'description'   => 'AI Converse (chat/assistant)',
                'is_superadmin' => 0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
    public function down(): void
    {
        if (!Schema::hasTable('modules')) return;
        DB::table('modules')->where('module_name', 'aiconverse')->delete();
    }
};
