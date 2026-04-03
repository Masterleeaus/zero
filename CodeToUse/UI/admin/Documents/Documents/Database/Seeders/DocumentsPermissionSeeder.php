<?php

namespace Modules\Documents\Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'view_documents', 'display_name' => 'View Documents', 'module_name' => 'Documents'],
            ['name' => 'add_documents', 'display_name' => 'Add Documents', 'module_name' => 'Documents'],
            ['name' => 'edit_documents', 'display_name' => 'Edit Documents', 'module_name' => 'Documents'],
            ['name' => 'delete_documents', 'display_name' => 'Delete Documents', 'module_name' => 'Documents'],
        ];
        DB::table('permissions')->insertOrIgnore($permissions);
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        if ($adminRoleId) {
            $permIds = DB::table('permissions')->where('module_name', 'Documents')->pluck('id');
            foreach ($permIds as $pid) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'role_id' => $adminRoleId,
                    'permission_id' => $pid
                ]);
            }
        }
    }
}
