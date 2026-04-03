<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('modules') || !Schema::hasTable('permissions')) {
            return;
        }

        $now = Carbon::now();

        // Figure out which column names exist in your schema
        $moduleCols = Schema::getColumnListing('modules');          // e.g. ['id','module_name','description',...]
        $permCols   = Schema::getColumnListing('permissions');      // e.g. ['id','name','display_name','module_id',...]

        $nameCol = null;
        if (in_array('module_name', $moduleCols)) {
            $nameCol = 'module_name';
        } elseif (in_array('name', $moduleCols)) {
            $nameCol = 'name';
        }

        // Find/create the Workflow module
        $moduleId = null;

        if ($nameCol) {
            $module = DB::table('modules')->where($nameCol, 'Workflow')->first();

            if (!$module) {
                $payload = [
                    $nameCol     => 'Workflow',
                    'description'=> 'Workflow module',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $moduleId = DB::table('modules')->insertGetId($payload);
            } else {
                $moduleId = $module->id;
            }
        } else {
            // Fallback if neither name column exists; just insert a row with whatever columns we can
            $payload = [
                'description'=> 'Workflow module',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (in_array('module_key', $moduleCols)) {
                $payload['module_key'] = 'workflow';
            }
            $moduleId = DB::table('modules')->insertGetId($payload);
        }

        // Upsert permission with a valid module_id
        $permName = 'view_workflow_reports';
        $existing = DB::table('permissions')->where('name', $permName)->first();

        $row = [
            'name'         => $permName,
            'display_name' => 'View Workflow Reports',
            'module_id'    => $moduleId,
            'updated_at'   => $now,
        ];
        if (in_array('created_at', $permCols)) $row['created_at'] = $now;
        if (in_array('guard_name', $permCols)) $row['guard_name'] = 'web';

        if (!$existing) {
            DB::table('permissions')->insert($row);
        } else {
            // Ensure module_id is set even if the row already existed without it
            DB::table('permissions')->where('id', $existing->id)->update($row);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('name', 'view_workflow_reports')->delete();
    }
};
