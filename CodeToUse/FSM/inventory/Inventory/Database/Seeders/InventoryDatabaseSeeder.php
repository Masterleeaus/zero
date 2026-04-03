<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::table('inventory_items')->count()) {
            DB::table('inventory_items')->insert([
                ['name' => 'Sample Item A', 'sku' => 'A-001', 'qty' => 10],
                ['name' => 'Sample Item B', 'sku' => 'B-001', 'qty' => 5],
            ]);
        }
    }
}
