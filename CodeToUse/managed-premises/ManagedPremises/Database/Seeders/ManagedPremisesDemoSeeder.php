<?php

namespace Modules\ManagedPremises\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManagedPremisesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $premiseId = DB::table('pm_properties')->insertGetId([
            'name' => 'Acme Office – Level 3',
            'address' => '123 Example St',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pm_property_units')->insert([
            ['property_id' => $premiseId, 'name' => 'Reception', 'created_at' => now(), 'updated_at' => now()],
            ['property_id' => $premiseId, 'name' => 'Kitchen', 'created_at' => now(), 'updated_at' => now()],
            ['property_id' => $premiseId, 'name' => 'Bathrooms', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('pm_property_checklists')->insert([
            'property_id' => $premiseId,
            'title' => 'Standard Office Clean',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
