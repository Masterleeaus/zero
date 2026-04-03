<?php

namespace Modules\Timesheet\Database\Seeders;

use Illuminate\Database\Seeder;

class TimesheetDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionTableSeeder::class,
        ]);

        // Optional marketplace tiles (guarded internally)
        $this->call([
            MarketPlaceSeederTableSeeder::class,
        ]);
    }
}
