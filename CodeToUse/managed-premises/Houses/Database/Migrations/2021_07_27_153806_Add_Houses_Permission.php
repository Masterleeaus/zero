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
use Modules\Houses\Entities\House;
use Illuminate\Support\Facades\Schema;
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
                'name' => 'add_house',
                'display_name' => 'Add House'
            ],
            [
                'name' => 'view_house',
                'display_name' => 'View House'
            ],
            [
                'name' => 'edit_house',
                'display_name' => 'Edit House'
            ],
            [
                'name' => 'delete_house',
                'display_name' => 'Delete House'
            ]
        ];

        $module = new Module();
        $module->module_name = 'houses';
        $module->description = 'User can view all Houses.';
        $module->saveQuietly();

        $module->permissions()->createMany($permissions);

        $all = ['add_house', 'view_house', 'edit_house', 'delete_house'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_NONE]);

        $companies = Company::all();

        // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            House::addModuleSetting($company);
        }

        Artisan::call('module:enable Houses');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        $module = Module::where('module_name', 'houses')->first();
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();

        Module::where('module_name', 'houses')->delete();
    }

};
