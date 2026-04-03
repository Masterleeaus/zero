<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // --- MODULE ROW ------------------------------------------------------
        if (Schema::hasTable('modules')) {
            $modCols = Schema::getColumnListing('modules');

            // Does a Workflow row already exist?
            $exists = DB::table('modules')->where('module_name', 'Workflow')->exists();

            if (!$exists) {
                $row = [
                    'module_name' => 'Workflow',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                // Only set these if the columns actually exist on this install
                if (in_array('module_description', $modCols)) {
                    $row['module_description'] = 'Workflow management starter module';
                }
                if (in_array('module_status', $modCols)) {
                    $row['module_status'] = 1;
                }

                DB::table('modules')->insert($row);
            }
        }

        // --- PERMISSION ROW --------------------------------------------------
        if (Schema::hasTable('permissions')) {
            $permCols = Schema::getColumnListing('permissions');

            $exists = DB::table('permissions')->where('name', 'view_workflow')->exists();
            if (!$exists) {
                $row = [
                    'name'       => 'view_workflow',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Optional/portable columns
                if (in_array('display_name', $permCols)) {
                    $row['display_name'] = 'View Workflow Module';
                }
                if (in_array('guard_name', $permCols)) {
                    $row['guard_name'] = config('auth.defaults.guard', 'web');
                }

                // Only attach to a module if FK column exists and the parent row is present
                if (in_array('module_id', $permCols) && Schema::hasTable('modules')) {
                    $moduleId = DB::table('modules')->where('module_name', 'Workflow')->value('id');
                    if ($moduleId) {
                        $row['module_id'] = $moduleId;
                    }
                }

                DB::table('permissions')->insert($row);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name','view_workflow')->delete();
        }
        // Intentionally leave the modules row intact to avoid surprises in prod
    }
};