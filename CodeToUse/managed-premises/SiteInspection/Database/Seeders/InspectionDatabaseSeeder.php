<?php

namespace Modules\Inspection\Database\Seeders;

use Modules\Inspection\Database\Seeders\InspectionRoleGrantSeeder;

use Modules\Inspection\Database\Seeders\InspectionPermissionSeeder;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class InspectionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    {
        Model::unguard();

        $this->call(InspectionPermissionSeeder::class);
    }
}
