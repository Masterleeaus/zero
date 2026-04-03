<?php

namespace Modules\Timesheet\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MarketPlaceSeederTableSeeder extends Seeder
{
    public function run(): void
    {
        // Optional: this module may provide marketplace tiles if your platform supports it.
        // Guard hard dependencies.
        if (!class_exists('Modules\LandingPage\Entities\MarketplacePageSetting')) {
            return;
        }

        Model::unguard();

        // Intentionally left minimal to avoid breaking installs.
        // Add marketplace seed data only when LandingPage module exists.
    }
}
