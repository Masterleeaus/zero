<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Defensive: menus schema differs across Worksuite forks
        if (!Schema::hasTable('menus')) {
            return;
        }
if (!Schema::hasTable('menus')) {
            return;
        }

        $columns = Schema::getColumnListing('menus');

        // In some Worksuite variants, menus has `key`. In others, it does not (parent_id + name + route only).
        $hasKey    = in_array('key', $columns, true);
        $hasName   = in_array('name', $columns, true);
        $hasLabel  = in_array('label', $columns, true);
        $hasModule = in_array('module', $columns, true) || in_array('module_name', $columns, true);

        $parentId = $this->findParentId($columns, [
            'assets', 'titan_assets', 'titan-assets', 'asset_manager', 'assets_manager',
        ]);

        // Create group container (idempotent)
        $groupId = $this->upsertMenu($columns, [
            'key'         => 'managedpremises',
            'parent_id'   => $parentId,
            'route'       => null,
            'label'       => 'Managed Premises',
            'name'        => 'Managed Premises',
            'type'        => 'group',
            'order'       => 55,
            'is_active'   => 1,
            'icon'        => 'fa fa-map-marker-alt',
            'module'      => 'managedpremises',
            'module_name' => 'managedpremises',
        ]);

        // Children
        $items = [
            ['key' => 'managedpremises.dashboard',  'route' => 'managedpremises.dashboard',         'label' => 'Dashboard',        'order' => 1],
            ['key' => 'managedpremises.premises',   'route' => 'managedpremises.properties.index', 'label' => 'Premises',         'order' => 2],
            ['key' => 'managedpremises.rooms',      'route' => 'managedpremises.rooms.index',      'label' => 'Rooms',            'order' => 10],
            ['key' => 'managedpremises.hazards',    'route' => 'managedpremises.hazards.index',    'label' => 'Hazards',          'order' => 11],
            ['key' => 'managedpremises.keys',       'route' => 'managedpremises.keys.index',       'label' => 'Keys & Access',    'order' => 12],
            ['key' => 'managedpremises.checklists', 'route' => 'managedpremises.checklists.index', 'label' => 'Checklists',       'order' => 13],
            ['key' => 'managedpremises.photos',     'route' => 'managedpremises.photos.index',     'label' => 'Photos',           'order' => 14],
            ['key' => 'managedpremises.documents',  'route' => 'managedpremises.documents.index',  'label' => 'Documents',        'order' => 15],
            ['key' => 'managedpremises.settings',   'route' => 'managedpremises.settings.index',   'label' => 'Settings',         'order' => 99],
        ];

        foreach ($items as $it) {
            $this->upsertMenu($columns, [
                'key'         => $it['key'],
                'parent_id'   => $groupId,
                'route'       => $it['route'],
                'label'       => $it['label'],
                'name'        => $it['label'],
                'type'        => 'item',
                'order'       => $it['order'],
                'is_active'   => 1,
                'icon'        => null,
                'module'      => 'managedpremises',
                'module_name' => 'managedpremises',
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }

        $columns = Schema::getColumnListing('menus');
        $hasKey = in_array('key', $columns, true);

        if ($hasKey) {
            // Delete children first
            DB::table('menus')->whereIn('key', [
                'managedpremises.dashboard',
                'managedpremises.premises',
                'managedpremises.rooms',
                'managedpremises.hazards',
                'managedpremises.keys',
                'managedpremises.checklists',
                'managedpremises.photos',
                'managedpremises.documents',
                'managedpremises.settings',
            ])->delete();

            DB::table('menus')->where('key', 'managedpremises')->delete();
            return;
        }

        // No `key` column: delete by module + route/name
        $routes = [
            'managedpremises.dashboard',
            'managedpremises.properties.index',
            'managedpremises.rooms.index',
            'managedpremises.hazards.index',
            'managedpremises.keys.index',
            'managedpremises.checklists.index',
            'managedpremises.photos.index',
            'managedpremises.documents.index',
            'managedpremises.settings.index',
        ];

        if (in_array('module', $columns, true)) {
            DB::table('menus')->where('module', 'managedpremises')->whereIn('route', $routes)->delete();
            DB::table('menus')->where('module', 'managedpremises')->where('name', 'Managed Premises')->delete();
            return;
        }

        if (in_array('module_name', $columns, true)) {
            DB::table('menus')->where('module_name', 'managedpremises')->whereIn('route', $routes)->delete();
            DB::table('menus')->where('module_name', 'managedpremises')->where('name', 'Managed Premises')->delete();
            return;
        }

        // Last resort: delete by route
        if (in_array('route', $columns, true)) {
            DB::table('menus')->whereIn('route', $routes)->delete();
        }
        if (in_array('name', $columns, true)) {
            DB::table('menus')->where('name', 'Managed Premises')->delete();
        }
    }

    private function findParentId(array $columns, array $possibleKeys): ?int
    {
        // Variant with `key`
        if (in_array('key', $columns, true)) {
            foreach ($possibleKeys as $key) {
                $row = DB::table('menus')->where('key', $key)->first();
                if ($row && isset($row->id)) {
                    return (int) $row->id;
                }
            }
            return null;
        }

        // Variant without `key`: try by common names
        $candidates = [
            'Assets',
            'Asset Manager',
            'Assets Manager',
            'Titan Assets',
        ];

        $q = DB::table('menus');
        if (in_array('parent_id', $columns, true)) {
            $q->whereNull('parent_id');
        }
        if (in_array('name', $columns, true)) {
            $row = (clone $q)->whereIn('name', $candidates)->first();
            if ($row && isset($row->id)) {
                return (int) $row->id;
            }
        }

        // Fallback: top-level "Assets" by route if your install uses routes for parents
        if (in_array('route', $columns, true)) {
            $row = (clone $q)->whereIn('route', ['assets.index', 'assetmanager.index', 'asset-manager.index'])->first();
            if ($row && isset($row->id)) {
                return (int) $row->id;
            }
        }

        return null;
    }

    private function upsertMenu(array $columns, array $data): int
    {
        $hasKey = in_array('key', $columns, true);

        $existing = null;

        if ($hasKey) {
            $existing = DB::table('menus')->where('key', $data['key'])->first();
        } else {
            // No `key`: use (module + route) when possible; otherwise (name + parent_id + type)
            if (in_array('module', $columns, true) && isset($data['route']) && $data['route']) {
                $existing = DB::table('menus')->where('module', 'managedpremises')->where('route', $data['route'])->first();
            } elseif (in_array('module_name', $columns, true) && isset($data['route']) && $data['route']) {
                $existing = DB::table('menus')->where('module_name', 'managedpremises')->where('route', $data['route'])->first();
            } elseif (in_array('name', $columns, true)) {
                $q = DB::table('menus')->where('name', $data['name'] ?? $data['label'] ?? 'Managed Premises');
                if (in_array('parent_id', $columns, true) && array_key_exists('parent_id', $data)) {
                    $q->where('parent_id', $data['parent_id']);
                }
                if (in_array('type', $columns, true) && isset($data['type'])) {
                    $q->where('type', $data['type']);
                }
                $existing = $q->first();
            }
        }

        $payload = $this->filterColumns($columns, array_merge($data, [
            'updated_at' => now(),
            'created_at' => now(),
        ]));

        // Some schemas use `name` instead of `label`
        if (in_array('name', $columns, true) && !isset($payload['name']) && isset($data['label'])) {
            $payload['name'] = $data['label'];
        }

        if ($existing && isset($existing->id)) {
            unset($payload['created_at']);
            DB::table('menus')->where('id', $existing->id)->update($payload);
            return (int) $existing->id;
        }

        // If schema doesn't allow null route, fallback to '#'
        if (array_key_exists('route', $payload) && $payload['route'] === null) {
            $payload['route'] = in_array('route', $columns, true) ? '#' : null;
        }

        return (int) DB::table('menus')->insertGetId($payload);
    }

    private function filterColumns(array $columns, array $payload): array
    {
        $out = [];
        foreach ($payload as $k => $v) {
            if (in_array($k, $columns, true)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }
};
