<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\Company;
use App\Models\Permission;
use App\Models\ModuleSetting;
use App\Models\PermissionRole;
use App\Models\PermissionType;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

      /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          // create module and permissions
        $permissions = [
            [
                'name'         => 'add_work_permits',
                'display_name' => 'Add Work Permit'
            ],
            [
                'name'         => 'view_work_permits',
                'display_name' => 'View Work Permit'
            ],
            [
                'name'         => 'edit_work_permits',
                'display_name' => 'Edit Work Permit'
            ],
            [
                'name'         => 'delete_work_permits',
                'display_name' => 'Delete Work Permit'
            ]
        ];

        $module              = new Module();
        $module->module_name = 'trworkpermits';
        $module->description = 'User can view all Work Permit.';
        $module->saveQuietly();
        $module->permissions()->createMany($permissions);
        $all = ['add_work_permits', 'view_work_permits', 'edit_work_permits', 'delete_work_permits'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_4_OWNED_2_NONE_5]);

        $companies = Company::all();

          // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            $roles = ['admin', 'employee', 'client'];
            ModuleSetting::createRoleSettingEntry('trworkpermits', $roles, $company);
        }

        Artisan::call('module:enable trworkpermits');
    }

      /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        $module      = Module::where('module_name', 'trworkpermits')->first();
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();
        Module::where('module_name', 'trworkpermits')->delete();
    }
};
