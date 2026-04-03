<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        if (!class_exists(\Spatie\Permission\Models\Permission::class)) {
            // spatie/laravel-permission not installed; skip
            return;
        }
        $Permission = \Spatie\Permission\Models\Permission::class;
        $Role = \Spatie\Permission\Models\Role::class;

        foreach (['inventory.view','inventory.manage'] as $perm) {
            if (!$Permission::where('name',$perm)->exists()) {
                $Permission::create(['name'=>$perm,'guard_name'=>config('auth.defaults.guard','web')]);
            }
        }

        // Optional: ensure 'admin' role has both
        if ($Role::where('name','admin')->exists()) {
            $admin = $Role::where('name','admin')->first();
            $admin->givePermissionTo(['inventory.view','inventory.manage']);
        }
    }
}
