<?php

namespace Modules\Contracts\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $data = [
            'title' => 'Contracts',
            'url'   => '/contracts',
            'icon'  => 'fa fa-file-signature',
            'order' => 70,
            'updated_at' => $now,
        ];
        $exists = DB::table('menus')->where('slug', 'contracts')->exists();
        if ($exists) {
            DB::table('menus')->where('slug', 'contracts')->update($data);
        } else {
            DB::table('menus')->insert(array_merge(['slug' => 'contracts', 'created_at' => $now], $data));
        }
    }
}
