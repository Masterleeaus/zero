<?php

namespace Modules\PropertyManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\PropertyManagement\Support\Permissions as PmPermissions;

class PropertyManagementDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();
    }

    private function seedPermissions(): void
    {
        $groupName = 'Property Management';

        $permissions = (new PmPermissions())->all();

        foreach ($permissions as $name) {
            $exists = DB::table('permissions')->where('name', $name)->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $name,
                    'display_name' => ucwords(str_replace(['.', '_'], [' ', ' '], $name)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Backwards-compat (older installs may reference these)
        $legacy = [
            ['name' => 'view_property', 'display_name' => 'View Properties'],
            ['name' => 'add_property', 'display_name' => 'Add Properties'],
            ['name' => 'edit_property', 'display_name' => 'Edit Properties'],
            ['name' => 'delete_property', 'display_name' => 'Delete Properties'],
        ];

        foreach ($legacy as $perm) {
            $exists = DB::table('permissions')->where('name', $perm['name'])->exists();
            if (!$exists) {
                DB::table('permissions')->insert(array_merge($perm, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
