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
use Modules\Units\Entities\Unit;
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
                'name'         => 'add_unit',
                'display_name' => 'Add Unit'
            ],
            [
                'name'         => 'view_unit',
                'display_name' => 'View Unit'
            ],
            [
                'name'         => 'edit_unit',
                'display_name' => 'Edit Unit'
            ],
            [
                'name'         => 'delete_unit',
                'display_name' => 'Delete Unit'
            ]
        ];

        $module              = new Module();
        $module->module_name = 'units';
        $module->description = 'User can view all Units.';
        $module->saveQuietly();

        $module->permissions()->createMany($permissions);

        $all = ['add_unit', 'view_unit', 'edit_unit', 'delete_unit'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_NONE]);

        $companies = Company::all();

        // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            Unit::addModuleSetting($company);
        }

        Artisan::call('module:enable units');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $module      = Module::where('module_name', 'units')->first();
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();

        Module::where('module_name', 'units')->delete();
    }
};
