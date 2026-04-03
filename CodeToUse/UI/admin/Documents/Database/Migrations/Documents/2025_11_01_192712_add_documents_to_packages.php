<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        if (!function_exists('schema') || !schema()->hasTable('packages')) {
            return;
        }
        $packages = DB::table('packages')->get();
        foreach ($packages as $pkg) {
            $mods = json_decode($pkg->module_in_package ?? '[]', true);
            if (!is_array($mods)) $mods = [];
            if (!in_array('documents', $mods) && in_array($pkg->name, ['Standard','Professional','Enterprise'])) {
                $mods[] = 'documents';
                DB::table('packages')->where('id', $pkg->id)
                  ->update(['module_in_package' => json_encode(array_values(array_unique($mods)))]);
            }
        }
    }
    public function down() {}
};
