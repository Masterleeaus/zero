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
                'name'         => 'add_package',
                'display_name' => 'Add Package'
            ],
            [
                'name'         => 'view_package',
                'display_name' => 'View Package'
            ],
            [
                'name'         => 'edit_package',
                'display_name' => 'Edit Package'
            ],
            [
                'name'         => 'delete_package',
                'display_name' => 'Delete Package'
            ]
        ];

        $module              = new Module();
        $module->module_name = 'trpackage';
        $module->description = 'User can view all Package.';
        $module->saveQuietly();

        $module->permissions()->createMany($permissions);

        $all = ['add_package', 'view_package', 'edit_package', 'delete_package'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_4_OWNED_2_NONE_5]);

        $companies = Company::all();

          // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            $roles = ['admin', 'employee'];
            ModuleSetting::createRoleSettingEntry('trpackage', $roles, $company);
        }

        Artisan::call('module:enable trpackage');
    }

      /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        $module      = Module::where('module_name', 'trpackage')->first();
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();
        Module::where('module_name', 'trpackage')->delete();
    }
};
