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
  use Modules\Units\Entities\TrAccessCard;
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
                'name'         => 'add_access_card',
                'display_name' => 'Add Card Akses'
            ],
            [
                'name'         => 'view_access_card',
                'display_name' => 'View Card Akses'
            ],
            [
                'name'         => 'edit_access_card',
                'display_name' => 'Edit Card Akses'
            ],
            [
                'name'         => 'delete_access_card',
                'display_name' => 'Delete Card Akses'
            ]
        ];

        $module              = new Module();
        $module->module_name = 'traccesscard';
        $module->description = 'User can view all Card Akses.';
        $module->saveQuietly();
        $module->permissions()->createMany($permissions);

        $all = ['add_access_card', 'view_access_card', 'edit_access_card', 'delete_access_card'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_4_OWNED_2_NONE_5]);

        $companies = Company::all();

          // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            $roles = ['admin', 'employee', 'client'];
            ModuleSetting::createRoleSettingEntry('traccesscard', $roles, $company);
        }

        Artisan::call('module:enable traccesscard');
    }

      /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $module      = Module::where('module_name', 'traccesscard')->first();
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();
        Module::where('module_name', 'traccesscard')->delete();
    }
};
