<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryMenuSeeder extends Seeder
{
    public function run(): void
    {
        $table = config('inventory.menu.table', env('INVENTORY_MENU_TABLE', 'menus'));
        if (!Schema::hasTable($table)) return;

        // Columns we try to use; adjust to your host app
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $hasSlug = in_array('slug', $columns);
        $hasUrl = in_array('url', $columns) || in_array('route', $columns);
        $hasName = in_array('name', $columns) || in_array('title', $columns);
        if (!$hasUrl || !$hasName) return;

        $exists = DB::table($table)->where(($hasSlug?'slug':'url'), $hasSlug?'inventory':'/inventory')->first();
        if (!$exists) {
            $payload = [];
            if ($hasSlug) $payload['slug'] = 'inventory';
            if ($hasName) $payload[$hasName ? (in_array('name',$columns)?'name':'title') : 'name'] = 'Inventory';
            if ($hasUrl)  $payload[in_array('url',$columns)?'url':'route'] = '/inventory';
            if (in_array('icon',$columns)) $payload['icon'] = 'inventory_2';
            if (in_array('permission',$columns)) $payload['permission'] = 'inventory.view';
            DB::table($table)->insert($payload);
        }
    }
}
