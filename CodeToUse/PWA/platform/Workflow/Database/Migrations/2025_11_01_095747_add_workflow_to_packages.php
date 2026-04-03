<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('packages')) return;

        $packages = DB::table('packages')->select('id','name','module_in_package')->get();
        foreach ($packages as $pkg) {
            $name = strtolower((string) $pkg->name);
            if (!in_array($name, ['professional','enterprise','pro','business','premium'])) continue;

            $mods = json_decode((string) $pkg->module_in_package, true);
            if (!is_array($mods)) $mods = [];
            if (!in_array('workflow', $mods)) {
                $mods[] = 'workflow';
                DB::table('packages')->where('id', $pkg->id)->update([
                    'module_in_package' => json_encode(array_values(array_unique($mods)))
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('packages')) return;

        $packages = DB::table('packages')->select('id','module_in_package')->get();
        foreach ($packages as $pkg) {
            $mods = json_decode((string) $pkg->module_in_package, true);
            if (!is_array($mods)) continue;
            $mods = array_values(array_filter($mods, fn($m) => $m !== 'workflow'));
            DB::table('packages')->where('id', $pkg->id)->update([
                'module_in_package' => json_encode($mods)
            ]);
        }
    }
};
