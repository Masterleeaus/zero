<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('packages')) {
            return;
        }

        $packages = DB::table('packages')->select('id','name','module_in_package')->get();
        foreach ($packages as $pkg) {
            $mods = json_decode($pkg->module_in_package, true);
            if (!is_array($mods)) { $mods = []; }
            // Decide which plans get Items: Pro & above by name heuristics
            $name = strtolower((string)$pkg->name);
            $shouldHave = str_contains($name,'pro') or str_contains($name,'business') or str_contains($name,'enterprise') or str_contains($name,'elite') or str_contains($name,'ultimate');
            if ($shouldHave && !in_array('items', $mods, true)) {
                $mods[] = 'items';
                DB::table('packages')->where('id',$pkg->id)->update(['module_in_package'=> json_encode(array_values(array_unique($mods)))]);
            }
        }
    }

    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('packages')) {
            return;
        }
        $packages = DB::table('packages')->select('id','module_in_package')->get();
        foreach ($packages as $pkg) {
            $mods = json_decode($pkg->module_in_package, true);
            if (!is_array($mods)) { continue; }
            $mods = array_values(array_filter($mods, fn($m) => $m !== 'items'));
            DB::table('packages')->where('id',$pkg->id)->update(['module_in_package'=> json_encode($mods)]);
        }
    }
};
