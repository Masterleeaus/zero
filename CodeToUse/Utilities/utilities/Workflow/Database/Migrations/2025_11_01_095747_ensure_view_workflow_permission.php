<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions')) return;

        $exists = DB::table('permissions')->where('name', 'view_workflow')->exists();
        if (!$exists) {
            DB::table('permissions')->insert([
                'name' => 'view_workflow',
                'display_name' => 'View Workflow',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions')) return;
        // do not delete permission on down to avoid breaking roles unexpectedly
    }
};
