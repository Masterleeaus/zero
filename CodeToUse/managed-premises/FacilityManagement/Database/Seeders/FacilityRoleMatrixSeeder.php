<?php
namespace Modules\FacilityManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilityRoleMatrixSeeder extends Seeder
{
    public function run(): void
    {
        if (!$this->hasRBAC()) { return; }

        $perms = config('facility_permissions', []);
        if (empty($perms)) return;

        // Ensure roles exist
        $viewerId  = $this->ensureRole('facility_viewer');
        $managerId = $this->ensureRole('facility_manager');
        $adminId   = $this->ensureRole('facility_admin');

        // Partition perms
        $viewPerms = array_values(array_filter($perms, fn($p)=>str_ends_with($p,'.view')));
        $managePerms = array_values(array_filter($perms, function($p){
            return preg_match('/\.(create|edit|delete|manage|schedule|complete|export|upload|read|assign|end)$/', $p);
        }));

        $allIds = $this->permIds($perms);
        $viewIds = $this->permIds($viewPerms);
        $manageIds = $this->permIds(array_unique(array_merge($viewPerms, $managePerms)));

        $this->sync($viewerId, $viewIds);
        $this->sync($managerId, $manageIds);
        $this->sync($adminId, $allIds);
    }

    protected function hasRBAC(): bool
    {
        try {
            DB::table('roles')->first();
            DB::table('permissions')->first();
            DB::table('role_has_permissions')->first();
            return true;
        } catch (\Throwable $e) { return false; }
    }

    protected function ensureRole(string $name): int
    {
        $r = DB::table('roles')->where('name',$name)->first();
        if (!$r) {
            DB::table('roles')->insert(['name'=>$name, 'guard_name'=>'web']);
            $r = DB::table('roles')->where('name',$name)->first();
        }
        return (int)$r->id;
    }

    protected function permIds(array $names): array
    {
        return DB::table('permissions')->whereIn('name',$names)->pluck('id')->all();
    }

    protected function sync(int $roleId, array $permIds): void
    {
        foreach ($permIds as $pid) {
            $exists = DB::table('role_has_permissions')->where(['role_id'=>$roleId,'permission_id'=>$pid])->exists();
            if (!$exists) {
                DB::table('role_has_permissions')->insert(['role_id'=>$roleId,'permission_id'=>$pid]);
            }
        }
    }
}
