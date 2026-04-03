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
                'name'         => 'add_trinoutpermit',
                'display_name' => 'Add In Out Permit Permission'
            ],
            [
                'name'         => 'view_trinoutpermit',
                'display_name' => 'View In Out Permit Permission'
            ],
            [
                'name'         => 'edit_trinoutpermit',
                'display_name' => 'Edit In Out Permit Permission'
            ],
            [
                'name'         => 'delete_trinoutpermit',
                'display_name' => 'Delete In Out Permit Permission'
            ]
        ];

        $module              = new Module();
        $module->module_name = 'trinoutpermit';
        $module->description = 'User can view all In Out Permit Permission.';
        $module->saveQuietly();
        $module->permissions()->createMany($permissions);

        $all = ['add_trinoutpermit', 'view_trinoutpermit', 'edit_trinoutpermit', 'delete_trinoutpermit'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_4_OWNED_2_NONE_5]);

        $companies = Company::all();

          // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            $roles = ['admin', 'employee', 'client'];
            ModuleSetting::createRoleSettingEntry('trinoutpermit', $roles, $company);
        }

        Artisan::call('module:enable trinoutpermit');
    }

      /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        $module      = Module::where('module_name', 'trinoutpermit')->first();
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();
        Module::where('module_name', 'trinoutpermit')->delete();
    }
};
