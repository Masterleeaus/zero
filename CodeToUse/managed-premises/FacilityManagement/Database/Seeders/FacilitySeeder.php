<?php
namespace Modules\FacilityManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $permissions = config('facility_permissions', []);
        if (empty($permissions)) return;

        if (self::tableExists('permissions')) {
            foreach ($permissions as $name) {
                if (!DB::table('permissions')->where('name',$name)->exists()) {
                    DB::table('permissions')->insert(['name'=>$name, 'guard_name'=>'web']);
                }
            }
        }

        if (self::tableExists('roles') && self::tableExists('role_has_permissions') && self::tableExists('permissions')) {
            $admin = DB::table('roles')->where('name','admin')->first();
            if ($admin) {
                $permIds = DB::table('permissions')->whereIn('name',$permissions)->pluck('id')->all();
                foreach ($permIds as $pid) {
                    $exists = DB::table('role_has_permissions')->where(['role_id'=>$admin->id,'permission_id'=>$pid])->exists();
                    if (!$exists) {
                        DB::table('role_has_permissions')->insert(['role_id'=>$admin->id,'permission_id'=>$pid]);
                    }
                }
            }
        }
    }

    private static function tableExists(string $name): bool
    {
        try { DB::table($name)->first(); return true; } catch (\Throwable $e) { return false; }
    }
}
