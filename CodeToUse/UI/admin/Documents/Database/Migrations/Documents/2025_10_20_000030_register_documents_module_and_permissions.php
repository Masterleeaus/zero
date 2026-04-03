<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure module row exists and get its id
        $moduleId = null;

        if (Schema::hasTable('modules')) {
            $moduleId = DB::table('modules')->where('module_name', 'documents')->value('id');
            if (!$moduleId) {
                $moduleId = DB::table('modules')->insertGetId([
                    'module_name' => 'documents',
                    'description' => 'Documents Module',
                    'status'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // Insert permission WITH module_id + guard_name
        if (Schema::hasTable('permissions')) {
            $permName = 'manage_documents';
            $exists = DB::table('permissions')->where('name', $permName)->exists();
            if (!$exists) {
                $payload = [
                    'name'         => $permName,
                    'display_name' => 'Manage Documents',
                    'module_id'    => $moduleId,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                if (Schema::hasColumn('permissions', 'guard_name')) {
                    $payload['guard_name'] = 'web';
                }

                DB::table('permissions')->insert($payload);
            }
        }

        // Grant permission to admin/superadmin roles
        if (
            Schema::hasTable('roles') &&
            Schema::hasTable('permissions') &&
            Schema::hasTable('role_has_permissions')
        ) {
            $permId = DB::table('permissions')->where('name', 'manage_documents')->value('id');

            if ($permId) {
                foreach (['admin', 'superadmin'] as $roleName) {
                    $roleId = DB::table('roles')->where('name', $roleName)->value('id');
                    if ($roleId) {
                        $linkExists = DB::table('role_has_permissions')
                            ->where('role_id', $roleId)
                            ->where('permission_id', $permId)
                            ->exists();

                        if (!$linkExists) {
                            DB::table('role_has_permissions')->insert([
                                'role_id' => $roleId,
                                'permission_id' => $permId,
                            ]);
                        }
                    }
                }
            }
        }

        // Add module to Pro/Enterprise packages if present
        if (Schema::hasTable('packages') && Schema::hasColumn('packages', 'module_in_package')) {
            $packages = DB::table('packages')->get();
            foreach ($packages as $pkg) {
                $mods = json_decode($pkg->module_in_package ?? '[]', true) ?: [];
                if (in_array($pkg->name, ['Pro', 'Enterprise'], true) && !in_array('documents', $mods, true)) {
                    $mods[] = 'documents';
                    DB::table('packages')
                        ->where('id', $pkg->id)
                        ->update(['module_in_package' => json_encode($mods)]);
                }
            }
        }
    }

    public function down(): void
    {
        // Safe rollback (keeps module & permission by default)
        /*
        if (Schema::hasTable('permissions') && Schema::hasTable('role_has_permissions')) {
            $permId = DB::table('permissions')->where('name', 'manage_documents')->value('id');
            if ($permId) {
                DB::table('role_has_permissions')->where('permission_id', $permId)->delete();
                DB::table('permissions')->where('id', $permId)->delete();
            }
        }
        */
    }
};
