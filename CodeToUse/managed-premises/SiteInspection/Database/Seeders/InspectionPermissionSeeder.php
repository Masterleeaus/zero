<?php

namespace Modules\Inspection\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class InspectionPermissionSeeder extends Seeder
{
    public function run()
    {
        try {
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $Permission = \Spatie\Permission\Models\Permission::class;
                foreach (config('siteinspection.permissions', []) as $p) {
                    $Permission::findOrCreate($p, config('auth.defaults.guard', 'web'));
                }
            } else {
                Log::warning('Spatie Permission not installed; skipping Inspection permissions.');
            }
        } catch (\Throwable $e) {
            Log::error('InspectionPermissionSeeder error: '.$e->getMessage());
        }
    }
}
