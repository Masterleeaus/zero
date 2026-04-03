<?php

use App\Models\Module;
use App\Models\Company;
use App\Models\Permission;
use App\Models\PermissionRole;
use Modules\TrNotes\Entities\Notes;
use Illuminate\Support\Facades\Artisan;
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
                'name' => 'add_notes',
                'display_name' => 'Add Tenancy'
            ],
            [
                'name' => 'view_notes',
                'display_name' => 'View Tenancy'
            ],
            [
                'name' => 'edit_notes',
                'display_name' => 'Edit Tenancy'
            ],
            [
                'name' => 'delete_notes',
                'display_name' => 'Delete Tenancy'
            ]
        ];

        $module = new Module();
        $module->module_name = 'trnotes';
        $module->description = 'User can view all Setting Notes for Tenancy.';
        $module->saveQuietly();

        $module->permissions()->createMany($permissions);

        $all = ['add_notes', 'view_notes', 'edit_notes', 'delete_notes'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_NONE]);

        $companies = Company::all();

        // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            Notes::addModuleSetting($company);
        }

        Artisan::call('module:enable trnotes');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        $module = Module::where('module_name', 'trnotes')->first();
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();
        Module::where('module_name', 'trnotes')->delete();
    }

};
