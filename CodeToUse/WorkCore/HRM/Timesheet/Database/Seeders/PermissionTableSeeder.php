<?php

namespace Modules\Timesheet\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class PermissionTableSeeder extends Seeder
{
    public function run(): void
    {
        // Keep cache safe
        try { Artisan::call('cache:clear'); } catch (\Throwable $e) {}

        $permissions = [
            'timesheet manage',
            'timesheet create',
            'timesheet edit',
            'timesheet delete',
            'timesheet submit',
            'timesheet approve',
            'timesheet report',
            'timesheet settings',
            'timesheet timer',
            'timesheet export',
        ];

        $companyRole = Role::where('name', 'company')->first();

        foreach ($permissions as $permName) {
            $perm = Permission::where('name', $permName)
                ->where('module', 'Timesheet')
                ->first();

            if (!$perm) {
                $perm = Permission::create([
                    'name' => $permName,
                    'guard_name' => 'web',
                    'module' => 'Timesheet',
                    'created_by' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Auto-attach to default company role if it exists
            if ($companyRole && method_exists($companyRole, 'hasPermission') && method_exists($companyRole, 'givePermission')) {
                if (!$companyRole->hasPermission($permName)) {
                    $companyRole->givePermission($perm);
                }
            }
        }
    }
}
