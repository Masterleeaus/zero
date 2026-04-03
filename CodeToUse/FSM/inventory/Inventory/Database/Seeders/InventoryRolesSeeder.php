<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;

class InventoryRolesSeeder extends Seeder
{
    public function run(): void
    {
        if (!class_exists(\Spatie\Permission\Models\Role::class)) return;
        $Role = \Spatie\Permission\Models\Role::class;

        $env = config('app.env', 'production');
        $guard = config('auth.defaults.guard','web');
        $view = 'inventory.view';
        $manage = 'inventory.manage';

        // Ensure permissions exist by invoking permissions seeder first if available
        if (class_exists(__NAMESPACE__.'\InventoryPermissionsSeeder')) {
            (new InventoryPermissionsSeeder())->run();
        }

        // Dev: create roles admin/manager/viewer with broad perms
        if ($env !== 'production') {
            $admin = $Role::firstOrCreate(['name'=>'admin','guard_name'=>$guard]);
            $manager = $Role::firstOrCreate(['name'=>'inventory-manager','guard_name'=>$guard]);
            $viewer = $Role::firstOrCreate(['name'=>'inventory-viewer','guard_name'=>$guard]);
            $admin->givePermissionTo([$view,$manage]);
            $manager->givePermissionTo([$view,$manage]);
            $viewer->givePermissionTo([$view]);
            return;
        }

        // Prod: only ensure roles exist; do not auto-assign manage to non-admin
        $Role::firstOrCreate(['name'=>'admin','guard_name'=>$guard]);
        $Role::firstOrCreate(['name'=>'inventory-manager','guard_name'=>$guard]);
        $Role::firstOrCreate(['name'=>'inventory-viewer','guard_name'=>$guard]);
    }
}
