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
use Modules\Security\Entities\Security;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

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
                'name' => 'add_security',
                'display_name' => 'Add Security'
            ],
            [
                'name' => 'view_security',
                'display_name' => 'View Security'
            ],
            [
                'name' => 'edit_security',
                'display_name' => 'Edit Security'
            ],
            [
                'name' => 'delete_security',
                'display_name' => 'Delete Security'
            ]
        ];

        $module = new Module();
        $module->module_name = 'security';
        $module->description = 'User can view all Security.';
        $module->saveQuietly();

        $module->permissions()->createMany($permissions);

        $all = ['add_security', 'view_security', 'edit_security', 'delete_security'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_NONE]);

        $companies = Company::all();

        // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            Security::addModuleSetting($company);
        }

        Artisan::call('module:enable security');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        $module = Module::where('module_name', 'security')->first();
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();

        Module::where('module_name', 'security')->delete();
    }

};
