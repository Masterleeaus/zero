<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $modules = ['orders', 'products', 'inventory', 'pos', 'crm', 'accounting', 'hrm', 'assets', 'marketing', 'builder', 'settings', 'staff'];
        $actions = ['view', 'add', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$module}"]);
            }
        }

        // create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $storeOwner = Role::firstOrCreate(['name' => 'store_owner']);
        $storeOwner->givePermissionTo(Permission::all()); // Give owners most rights inside their tenant scopes

        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->givePermissionTo([
            'view orders', 'add orders', 'edit orders',
            'view products', 'view inventory',
            'view pos', 'add pos',
            'view crm', 'add crm', 'edit crm', 'view settings'
        ]);

        $customer = Role::firstOrCreate(['name' => 'customer']);
        // customers don't get these dashboard permissions right now
    }
}
