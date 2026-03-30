<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        // Safety checks
        if (!Schema::hasTable('modules') || !Schema::hasTable('permissions')) {
            return;
        }

        $now = Carbon::now();

        // 1) Ensure a Workflow module row exists and capture its id
        // Worksuite's `modules` table typically uses `module_name` (your schema does).
        // Avoid referencing non-existent columns (e.g. `name`) to prevent migration failure.
        $moduleQuery = DB::table('modules');

        if (Schema::hasColumn('modules', 'module_name')) {
            $moduleQuery->where('module_name', 'Workflow');
        } elseif (Schema::hasColumn('modules', 'name')) {
            // Support older forks that used `name`
            $moduleQuery->where('name', 'Workflow');
        } else {
            // Unknown schema; don't hard-fail the whole migration.
            return;
        }

        $module = $moduleQuery->first();

        if (!$module) {
            // Insert using only columns that exist.
            $columns = DB::getSchemaBuilder()->getColumnListing('modules');
            $payload = [
                'description' => 'Workflow module',
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
            if (in_array('module_name', $columns)) $payload['module_name'] = 'Workflow';
            if (in_array('name', $columns))        $payload['name']        = 'Workflow';
            $moduleId = DB::table('modules')->insertGetId($payload);
        } else {
            $moduleId = $module->id;
        }

        // 2) Upsert permissions and bind them to this module_id
        $perms = [
            ['name' => 'create_workflow',          'display_name' => 'Create Workflow'],
            ['name' => 'view_workflow',            'display_name' => 'View Workflow'],
            ['name' => 'edit_workflow',            'display_name' => 'Edit Workflow'],
            ['name' => 'delete_workflow',          'display_name' => 'Delete Workflow'],
            ['name' => 'view_workflow_reports',    'display_name' => 'View Workflow Reports'],
        ];

        // Check which columns exist in permissions to avoid insert failures (e.g., guard_name)
        $permCols = Schema::hasTable('permissions')
            ? DB::getSchemaBuilder()->getColumnListing('permissions')
            : [];

        foreach ($perms as $p) {
            $exists = DB::table('permissions')->where('name', $p['name'])->first();

            $row = [
                'name'         => $p['name'],
                'display_name' => $p['display_name'],
                'module_id'    => $moduleId,
                'updated_at'   => $now,
            ];
            if (in_array('created_at', $permCols)) $row['created_at'] = $now;
            if (in_array('guard_name', $permCols)) $row['guard_name'] = 'web';

            if (!$exists) {
                DB::table('permissions')->insert($row);
            } else {
                // Ensure module_id is set
                $update = ['module_id' => $moduleId, 'updated_at' => $now];
                if (in_array('display_name', $permCols)) $update['display_name'] = $p['display_name'];
                DB::table('permissions')->where('id', $exists->id)->update($update);
            }
        }

        // 3) Optionally assign to admin role
        if (Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
            $adminRole = DB::table('roles')
                ->whereIn('name', ['admin', 'Admin', 'administrator'])
                ->first();

            if ($adminRole) {
                $permIds = DB::table('permissions')
                    ->whereIn('name', array_column($perms, 'name'))
                    ->pluck('id')
                    ->all();

                foreach ($permIds as $pid) {
                    $link = DB::table('role_has_permissions')
                        ->where('role_id', $adminRole->id)
                        ->where('permission_id', $pid)
                        ->first();

                    if (!$link) {
                        DB::table('role_has_permissions')->insert([
                            'role_id'       => $adminRole->id,
                            'permission_id' => $pid,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // You can optionally remove the workflow permissions; leaving no-op is usually safer in shared systems
        // Example rollback (commented out):
        // DB::table('permissions')->whereIn('name', [
        //     'create_workflow','view_workflow','edit_workflow','delete_workflow','view_workflow_reports'
        // ])->delete();
    }
};
