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
use Modules\FieldItems\Entities\Item;
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
        //
        // create module and permissions
        $permissions = [
            [
                'name' => 'add_item',
                'display_name' => 'Add Item'
            ],
            [
                'name' => 'view_item',
                'display_name' => 'View Item'
            ],
            [
                'name' => 'edit_item',
                'display_name' => 'Edit Item'
            ],
            [
                'name' => 'delete_item',
                'display_name' => 'Delete Item'
            ],
            [
                'name' => 'manage_item_category',
                'display_name' => 'Manage Item Category'
            ],
            [
                'name' => 'manage_item_sub_category',
                'display_name' => 'Manage Item Sub Category'
            ]
        ];

        $module = Module::where('module_name','items')->first();
        if(!$module){
            $module = new Module();
            $module->module_name = 'items';
            $module->description = 'User can manage all Items.';
            $module->saveQuietly();
        }
        
        foreach($permissions as $index => $permission){
            $data_permission = Permission::where('name', $permission['name'])->first();
            if($data_permission){
                unset($permissions[$index]);
            }
        }

        if(count($permissions) > 0){
            $module->permissions()->createMany($permissions);
        }

        $all = ['add_item', 'view_item', 'edit_item', 'delete_item','manage_item_category','manage_item_sub_category'];
        Permission::whereIn('name', $all)->update(['allowed_permissions' => Permission::ALL_NONE]);

        $companies = Company::all();

        // We will insert these for the new company from event listener
        foreach ($companies as $company) {
            Item::addModuleSetting($company);
        }

        // removed: bad nwidart enable call
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        $module = Module::where('module_name', 'items')->first();
        if (!$module) { return; }
        $permisisons = Permission::where('module_id', $module->id)->get()->pluck('id')->toArray();
        PermissionRole::whereIn('permission_id', $permisisons)->delete();

        Module::where('module_name', 'items')->delete();
        ModuleSetting::where('module_name', 'items')->delete();
    }
};
