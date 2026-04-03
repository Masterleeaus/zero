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
use Modules\Suppliers\Entities\Supplier;

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
                'name' => 'add_suppliers',
                'display_name' => 'Add Suppliers'
            ],
            [
                'name' => 'view_suppliers',
                'display_name' => 'View Suppliers'
            ],
            [
                'name' => 'edit_suppliers',
                'display_name' => 'Edit Suppliers'
            ],
            [
                'name' => 'delete_suppliers',
                'display_name' => 'Delete Suppliers'
            ]
        ];

        $module = new Module();
        $module->module_name = 'suppliers';
        $module->description = 'User can view all Suppliers.';
        $module->saveQuietly();

        $module->permissions()->createMany($permissions);

        $all = ['add_suppliers', 'view_suppliers', 'edit_suppliers', 'delete_suppliers'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_NONE]);

        $companies = Company::all();

        // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            Supplier::addModuleSetting($company);
        }

        Artisan::call('module:enable suppliers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        $module = Module::where('module_name', 'suppliers')->first();
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();

        Module::where('module_name', 'suppliers')->delete();
    }

};
