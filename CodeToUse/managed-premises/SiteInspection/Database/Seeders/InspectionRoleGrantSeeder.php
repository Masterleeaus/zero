<?php

namespace Modules\Inspection\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class InspectionRoleGrantSeeder extends Seeder
{
    public function run()
    {
        try {
            if (!class_exists(\Spatie\Permission\Models\Role::class) || !class_exists(\Spatie\Permission\Models\Permission::class)) {
                Log::warning('Spatie Permission not installed; skipping Inspection role grants.');
                return;
            }

            $Role = \Spatie\Permission\Models\Role::class;
            $Permission = \Spatie\Permission\Models\Permission::class;

            $roleNames = ['super_admin','admin'];
            $perms = config('siteinspection.permissions', []);

            foreach ($roleNames as $rname) {
                $role = $Role::where('name', $rname)->first();
                if (!$role) { continue; }
                foreach ($perms as $pname) {
                    $perm = $Permission::where('name', $pname)->first();
                    if ($perm) {
                        $role->givePermissionTo($perm);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('InspectionRoleGrantSeeder error: '.$e->getMessage());
        }
    }
}
