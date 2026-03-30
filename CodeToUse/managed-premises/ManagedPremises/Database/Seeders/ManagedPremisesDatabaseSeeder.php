<?php

namespace Modules\ManagedPremises\Database\Seeders;

use Illuminate\Database\Seeder;

class ManagedPremisesDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoManagedPremisesSeeder::class,
        ]);
    }
}
