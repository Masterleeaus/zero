<?php

namespace App\Extensions\TitanRewind\System\Services;

use Illuminate\Support\Facades\DB;

class RewindImpactAnalyzer
{
    public function __construct(private readonly RewindDependencyGraphService $graph)
    {
    }

    public function analyze(array $context): array
    {
        $graph = $this->graph->build($context);
        $nodes = collect($graph['nodes'] ?? []);

        $downstream = $nodes->map(function (array $node) {
            return [
                'child_process_id' => $node['process_id'] ?? null,
                'child_entity_type' => $node['entity_type'] ?? null,
                'child_entity_id' => $node['entity_id'] ?? null,
                'relationship_type' => $node['relationship_type'] ?? 'cascade',
                'depth' => (int) ($node['depth'] ?? 1),
                'action_required' => $node['action_required'] ?? ((bool) ($node['must_reissue'] ?? false) ? 'reissue' : 'reuse'),
                'can_reuse' => (bool) ($node['can_reuse'] ?? true),
                'must_reissue' => (bool) ($node['must_reissue'] ?? false),
                'origin' => $node['origin'] ?? 'graph',
            ];
        })->values()->all();

        $affectedUsers = [];
        if (!empty($context['user_id'])) {
            $affectedUsers[] = (int) $context['user_id'];
        }
        if (!empty($context['process_id']) && DB::getSchemaBuilder()->hasTable('tz_processes')) {
            $processUsers = DB::table('tz_processes')
                ->where('company_id', $context['company_id'])
                ->whereIn('process_id', array_values(array_filter(array_column($downstream, 'child_process_id'))))
                ->pluck('user_id')
                ->filter()
                ->map(fn ($v) => (int) $v)
                ->all();
            $affectedUsers = array_values(array_unique(array_merge($affectedUsers, $processUsers)));
        }

        return [
            'original_process_id' => $context['process_id'] ?? null,
            'entity_type' => $context['entity_type'] ?? null,
            'entity_id' => $context['entity_id'] ?? null,
            'graph' => $graph,
            'affected_processes' => array_values(array_filter(array_column($downstream, 'child_process_id'))),
            'affected_users' => $affectedUsers,
            'downstream' => $downstream,
            'counts' => [
                'downstream' => count($downstream),
                'must_reissue' => count(array_filter($downstream, fn ($item) => $item['must_reissue'])),
                'reusable' => count(array_filter($downstream, fn ($item) => $item['can_reuse'])),
                'affected_users' => count($affectedUsers),
                'max_depth' => $nodes->max('depth') ?? 0,
            ],
        ];
    }
}
