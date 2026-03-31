<?php

namespace App\Extensions\TitanRewind\System\Services;

use Illuminate\Support\Facades\DB;

class RewindDependencyGraphService
{
    public function build(array $context): array
    {
        $companyId = $context['company_id'];
        $processId = $context['process_id'] ?? null;
        $entityType = $context['entity_type'] ?? null;
        $entityId = $context['entity_id'] ?? null;

        $nodes = collect();

        if ($processId && DB::getSchemaBuilder()->hasTable('tz_process_dependencies')) {
            $nodes = $this->loadFromProcessDependencies($companyId, $processId);
        }

        if ($nodes->isEmpty() && $processId && DB::getSchemaBuilder()->hasTable('tz_rewind_links')) {
            $nodes = DB::table('tz_rewind_links')
                ->where('company_id', $companyId)
                ->where('parent_process_id', $processId)
                ->get()
                ->map(fn ($link) => [
                    'process_id' => $link->child_process_id,
                    'entity_type' => $link->child_entity_type,
                    'entity_id' => $link->child_entity_id,
                    'depth' => (int) ($link->depth ?? 1),
                    'relationship_type' => $link->relationship_type,
                    'action_required' => $link->action_required,
                    'must_reissue' => (bool) $link->must_reissue,
                    'can_reuse' => (bool) $link->can_reuse,
                    'origin' => 'existing-link',
                ]);
        }

        if ($nodes->isEmpty() && $processId && DB::getSchemaBuilder()->hasTable('tz_processes')) {
            $nodes = DB::table('tz_processes')
                ->where('company_id', $companyId)
                ->where(function ($query) use ($processId) {
                    $query->where('parent_process_id', $processId)
                        ->orWhere('created_from_process', $processId);
                })
                ->get()
                ->map(fn ($row) => [
                    'process_id' => $row->process_id,
                    'entity_type' => $row->entity_type ?? null,
                    'entity_id' => $row->processed_entity_id ?? null,
                    'depth' => 1,
                    'relationship_type' => 'process-link',
                    'action_required' => 'reuse',
                    'must_reissue' => false,
                    'can_reuse' => true,
                    'origin' => 'process-table',
                ]);
        }

        if ($nodes->isEmpty()) {
            $nodes = collect($this->domainRuleNodes($entityType, $entityId));
        }

        return [
            'root' => compact('processId', 'entityType', 'entityId'),
            'nodes' => $nodes->values()->all(),
        ];
    }

    private function loadFromProcessDependencies(int $companyId, string $processId)
    {
        $rows = DB::table('tz_process_dependencies')
            ->where('company_id', $companyId)
            ->where('parent_process_id', $processId)
            ->get();

        return $rows->map(function ($row) use ($companyId) {
            $process = DB::getSchemaBuilder()->hasTable('tz_processes')
                ? DB::table('tz_processes')->where('company_id', $companyId)->where('process_id', $row->child_process_id)->first()
                : null;
            $entityType = $process->entity_type ?? null;
            $entityId = $process->processed_entity_id ?? null;
            $mustReissue = $entityType === 'payments';
            return [
                'process_id' => $row->child_process_id,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'depth' => 1,
                'relationship_type' => $row->relationship_type ?? 'depends_on',
                'action_required' => $mustReissue ? 'reissue' : 'reuse',
                'must_reissue' => $mustReissue,
                'can_reuse' => !$mustReissue,
                'origin' => 'process-dependency',
            ];
        });
    }

    private function domainRuleNodes(?string $entityType, mixed $entityId): array
    {
        $rules = config('titan-rewind.domain_rules', []);
        $nodes = [];
        $visited = [];
        $walk = function (string $type, int $depth = 1) use (&$walk, &$nodes, &$visited, $entityId, $rules) {
            if (isset($visited[$type])) {
                return;
            }
            $visited[$type] = true;
            $rule = $rules[$type] ?? ['children' => [], 'default_reuse' => true];
            foreach ($rule['children'] as $childType) {
                $childRule = $rules[$childType] ?? ['children' => [], 'default_reuse' => true];
                $mustReissue = !($childRule['default_reuse'] ?? true);
                $nodes[] = [
                    'process_id' => null,
                    'entity_type' => $childType,
                    'entity_id' => null,
                    'depth' => $depth,
                    'relationship_type' => 'domain-rule',
                    'action_required' => $mustReissue ? 'reissue' : 'reuse',
                    'must_reissue' => $mustReissue,
                    'can_reuse' => !$mustReissue,
                    'parent_entity_type' => $type,
                    'parent_entity_id' => $entityId,
                    'origin' => 'domain-rule',
                ];
                $walk($childType, $depth + 1);
            }
        };

        if ($entityType) {
            $walk($entityType);
        }

        return $nodes;
    }
}
