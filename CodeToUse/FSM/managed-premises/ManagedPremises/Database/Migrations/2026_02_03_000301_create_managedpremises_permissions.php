<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $moduleName = 'managedpremises';

        // Ensure module record exists (for Packages + activation)
        $module = Module::firstOrCreate(['module_name' => $moduleName]);

        $permissions = [
            ['name' => 'managedpremises.view',     'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'managedpremises.create',   'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'managedpremises.update',   'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'managedpremises.delete',   'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'managedpremises.settings', 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'managedpremises.calendar.view', 'allowed_permissions' => Permission::ALL_NONE],
        ];

        foreach ($permissions as $permissionData) {
            $permission = Permission::updateOrCreate(
                [
                    'name' => $permissionData['name'],
                    'module_id' => $module->id,
                ],
                [
                    'display_name' => ucwords(str_replace(['_', '.'], ' ', $permissionData['name'])),
                    'is_custom' => 1,
                    'allowed_permissions' => $permissionData['allowed_permissions'],
                ]
            );

            foreach (Company::all() as $company) {
                // Attach to company admin role (All)
                $role = Role::where('name', 'admin')->where('company_id', $company->id)->first();
                if ($role) {
                    $permissionRole = PermissionRole::firstOrNew([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id,
                    ]);
                    $permissionRole->permission_type_id = 4; // All
                    $permissionRole->save();
                }

                // Ensure module is enabled for the company in module_settings if that table exists
                if (Schema::hasTable('module_settings')) {
                    $cols = Schema::getColumnListing('module_settings');
                    $data = ['module_name' => $moduleName, 'company_id' => $company->id];

                    if (in_array('status', $cols, true)) {
                        $data['status'] = 'active';
                    } elseif (in_array('is_active', $cols, true)) {
                        $data['is_active'] = 1;
                    }

                    // Some forks include user_id; keep it nullable if present
                    if (in_array('user_id', $cols, true) && !isset($data['user_id'])) {
                        $data['user_id'] = null;
                    }

                    // Upsert defensively (no model dependency)
                    $exists = DB::table('module_settings')
                        ->where('company_id', $company->id)
                        ->where('module_name', $moduleName)
                        ->exists();

                    if (!$exists) {
                        DB::table('module_settings')->insert($data);
                    } else {
                        // keep active
                        $update = [];
                        if (isset($data['status'])) $update['status'] = $data['status'];
                        if (isset($data['is_active'])) $update['is_active'] = $data['is_active'];
                        if (!empty($update)) {
                            DB::table('module_settings')
                                ->where('company_id', $company->id)
                                ->where('module_name', $moduleName)
                                ->update($update);
                        }
                    }
                }
            }

            // Also grant to all admin users (All)
            if (method_exists(User::class, 'allAdmins')) {
                $adminUsers = User::allAdmins();
            } else {
                $adminUsers = User::where('role_id', 1)->get(); // fallback
            }

            foreach ($adminUsers as $adminUser) {
                $userPermission = UserPermission::firstOrNew([
                    'user_id' => $adminUser->id,
                    'permission_id' => $permission->id,
                ]);
                $userPermission->permission_type_id = 4; // All
                $userPermission->save();
            }
        }
    }

    public function down(): void
    {
        // No-op (safe)
    }
};
