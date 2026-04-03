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
use Modules\Kontrak\Entities\Kontrak;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
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
                'name' => 'add_kontrak',
                'display_name' => 'Add Kontrak'
            ],
            [
                'name' => 'view_kontrak',
                'display_name' => 'View Kontrak'
            ],
            [
                'name' => 'edit_kontrak',
                'display_name' => 'Edit Kontrak'
            ],
            [
                'name' => 'delete_kontrak',
                'display_name' => 'Delete Kontrak'
            ]
        ];

        $module = new Module();
        $module->module_name = 'kontrak';
        $module->description = 'User can view all Kontrak.';
        $module->saveQuietly();

        $module->permissions()->createMany($permissions);

        $all = ['add_kontrak', 'view_kontrak', 'edit_kontrak', 'delete_kontrak'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_NONE]);

        $companies = Company::all();

        // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            Kontrak::addModuleSetting($company);
        }

        Artisan::call('module:enable kontrak');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        $module = Module::where('module_name', 'kontrak')->first();
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();

        Module::where('module_name', 'kontrak')->delete();
    }

};
