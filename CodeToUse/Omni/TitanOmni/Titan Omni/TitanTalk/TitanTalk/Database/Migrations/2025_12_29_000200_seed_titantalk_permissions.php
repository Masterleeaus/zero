<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $cols = Schema::getColumnListing('permissions');
        if (!in_array('name', $cols, true)) {
            return;
        }

        // Pull permission names from config
        $permCfg = config('titantalk-permissions') ?: [];
        $names = [];
        foreach ($permCfg as $group => $actions) {
            if (!is_array($actions)) continue;
            foreach ($actions as $action => $permName) {
                if (is_string($permName) && $permName !== '') {
                    $names[$permName] = [$group, $action];
                }
            }
        }

        if (empty($names)) {
            return;
        }

        $hasDisplay = in_array('display_name', $cols, true);
        $hasDesc    = in_array('description', $cols, true);
        $hasModuleId= in_array('module_id', $cols, true);
        $hasCustom  = in_array('is_custom', $cols, true);
        $hasCreated = in_array('created_at', $cols, true);
        $hasUpdated = in_array('updated_at', $cols, true);

        $moduleId = null;
        if ($hasModuleId && Schema::hasTable('modules') && in_array('id', Schema::getColumnListing('modules'), true)) {
            // best-effort match
            try {
                $moduleId = DB::table('modules')
                    ->whereIn('module_name', ['TitanTalk', 'Titan Talk', 'TitanTalkModule'])
                    ->value('id');
            } catch (\Throwable $e) {
                $moduleId = null;
            }
        }

        foreach ($names as $name => [$group, $action]) {
            // Skip if exists
            try {
                $exists = DB::table('permissions')->where('name', $name)->exists();
                if ($exists) continue;
            } catch (\Throwable $e) {
                continue;
            }

            $row = ['name' => $name];

            if ($hasDisplay) {
                $row['display_name'] = 'TitanTalk: ' . ucfirst($group) . ' ' . ucfirst($action);
            }
            if ($hasDesc) {
                $row['description'] = 'TitanTalk permission (' . $group . ':' . $action . ')';
            }
            if ($hasModuleId) {
                $row['module_id'] = $moduleId;
            }
            if ($hasCustom) {
                $row['is_custom'] = 0;
            }
            if ($hasCreated) $row['created_at'] = now();
            if ($hasUpdated) $row['updated_at'] = now();

            try {
                DB::table('permissions')->insert($row);
            } catch (\Throwable $e) {
                // ignore insert failures to remain non-destructive
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasColumn('permissions', 'name')) {
            return;
        }
        $permCfg = config('titantalk-permissions') ?: [];
        $names = [];
        foreach ($permCfg as $actions) {
            if (!is_array($actions)) continue;
            foreach ($actions as $permName) {
                if (is_string($permName) && $permName !== '') $names[] = $permName;
            }
        }
        if (empty($names)) return;

        try {
            DB::table('permissions')->whereIn('name', $names)->delete();
        } catch (\Throwable $e) {}
    }
};
