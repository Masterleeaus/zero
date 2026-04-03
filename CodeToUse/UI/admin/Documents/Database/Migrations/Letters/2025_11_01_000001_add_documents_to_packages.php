<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $packages = DB::table('packages')->get();
        foreach ($packages as $pkg) {
            $mods = json_decode($pkg->module_in_package, true) ?? [];
            if (in_array($pkg->name, ['Professional', 'Enterprise'])) {
                $mods[] = 'documents';
                DB::table('packages')
                    ->where('id', $pkg->id)
                    ->update(['module_in_package' => json_encode(array_unique($mods))]);
            }
        }
    }
    public function down(): void
    {
        $packages = DB::table('packages')->get();
        foreach ($packages as $pkg) {
            $mods = array_diff(json_decode($pkg->module_in_package, true) ?? [], ['documents']);
            DB::table('packages')
                ->where('id', $pkg->id)
                ->update(['module_in_package' => json_encode($mods)]);
        }
    }
};
