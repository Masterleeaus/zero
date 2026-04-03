<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('modules')) {
            return;
        }

        // Resolve module_id in a way that works across WorkSuite variants.
        $moduleId = null;
        try {
            $candidates = ['titantalk', 'TitanTalk', 'titan-talk'];
            foreach ($candidates as $name) {
                $row = DB::table('modules')->where('module_name', $name)->first();
                if ($row && isset($row->id)) { $moduleId = (int) $row->id; break; }
            }
            if (!$moduleId) {
                // Some installs store alias under a different column; try a looser match.
                $row = DB::table('modules')->where('module_name', 'like', '%titantalk%')->first();
                if ($row && isset($row->id)) $moduleId = (int) $row->id;
            }
        } catch (\Throwable $e) {
            return;
        }

        if (!$moduleId) {
            return;
        }

        $now = now();

        // Minimal set used by Titan Talk routes + UI.
        $perms = [
            ['name' => 'titantalk.intents.view',     'display' => 'TitanTalk: View Intents',     'desc' => 'View Titan Talk intents'],
            ['name' => 'titantalk.intents.create',   'display' => 'TitanTalk: Create Intents',   'desc' => 'Create Titan Talk intents'],
            ['name' => 'titantalk.intents.update',   'display' => 'TitanTalk: Update Intents',   'desc' => 'Update Titan Talk intents'],
            ['name' => 'titantalk.intents.delete',   'display' => 'TitanTalk: Delete Intents',   'desc' => 'Delete Titan Talk intents'],

            ['name' => 'titantalk.entities.view',    'display' => 'TitanTalk: View Entities',    'desc' => 'View Titan Talk entities'],
            ['name' => 'titantalk.entities.create',  'display' => 'TitanTalk: Create Entities',  'desc' => 'Create Titan Talk entities'],
            ['name' => 'titantalk.entities.update',  'display' => 'TitanTalk: Update Entities',  'desc' => 'Update Titan Talk entities'],
            ['name' => 'titantalk.entities.delete',  'display' => 'TitanTalk: Delete Entities',  'desc' => 'Delete Titan Talk entities'],

            ['name' => 'titantalk.training.view',    'display' => 'TitanTalk: View Training',    'desc' => 'View Titan Talk training phrases'],
            ['name' => 'titantalk.training.create',  'display' => 'TitanTalk: Create Training',  'desc' => 'Create Titan Talk training phrases'],
            ['name' => 'titantalk.training.delete',  'display' => 'TitanTalk: Delete Training',  'desc' => 'Delete Titan Talk training phrases'],

            ['name' => 'titantalk.settings.manage',  'display' => 'TitanTalk: Manage Settings',  'desc' => 'Manage Titan Talk channel settings'],
        ];

        // Detect schema columns (WorkSuite versions vary).
        $hasDisplay = Schema::hasColumn('permissions', 'display_name');
        $hasDesc    = Schema::hasColumn('permissions', 'description');
        $hasModule  = Schema::hasColumn('permissions', 'module_id');
        $hasCustom  = Schema::hasColumn('permissions', 'is_custom');
        $hasAllowed = Schema::hasColumn('permissions', 'allowed_permissions');
        $hasCreated = Schema::hasColumn('permissions', 'created_at');
        $hasUpdated = Schema::hasColumn('permissions', 'updated_at');

        foreach ($perms as $p) {
            try {
                $q = DB::table('permissions')->where('name', $p['name']);
                if ($hasModule) $q->where('module_id', $moduleId);
                if ($q->exists()) continue;

                $row = ['name' => $p['name']];
                if ($hasDisplay) $row['display_name'] = $p['display'];
                if ($hasDesc)    $row['description']  = $p['desc'];
                if ($hasModule)  $row['module_id']    = $moduleId;
                if ($hasCustom)  $row['is_custom']    = 0;
                if ($hasAllowed) $row['allowed_permissions'] = null;
                if ($hasCreated) $row['created_at'] = $now;
                if ($hasUpdated) $row['updated_at'] = $now;

                DB::table('permissions')->insert($row);
            } catch (\Throwable $e) {
                // safe skip
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $names = [
            'titantalk.intents.view','titantalk.intents.create','titantalk.intents.update','titantalk.intents.delete',
            'titantalk.entities.view','titantalk.entities.create','titantalk.entities.update','titantalk.entities.delete',
            'titantalk.training.view','titantalk.training.create','titantalk.training.delete',
            'titantalk.settings.manage',
        ];

        try {
            DB::table('permissions')->whereIn('name', $names)->delete();
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
