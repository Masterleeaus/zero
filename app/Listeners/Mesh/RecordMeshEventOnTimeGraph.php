<?php

declare(strict_types=1);

namespace App\Listeners\Mesh;

use App\Events\Mesh\MeshDispatchCompleted;
use App\Events\Mesh\MeshDispatchRequested;
use App\Events\Mesh\MeshNodeHandshaked;
use App\Events\Mesh\MeshTrustChanged;
use Illuminate\Support\Facades\Log;

/**
 * Record mesh lifecycle events on the Execution Time Graph (Module 06 integration).
 *
 * Tolerant integration: skips gracefully if ExecutionTimeGraphService is not yet available.
 */
class RecordMeshEventOnTimeGraph
{
    public function handle(object $event): void
    {
        if (! class_exists(\App\Services\TimeGraph\ExecutionTimeGraphService::class)) {
            Log::debug('RecordMeshEventOnTimeGraph: ExecutionTimeGraphService not available, skipping.', [
                'event' => get_class($event),
            ]);
            return;
        }

        try {
            $entry = match (true) {
                $event instanceof MeshNodeHandshaked     => ['type' => 'mesh_handshake',          'ref' => $event->node->node_id],
                $event instanceof MeshDispatchRequested  => ['type' => 'mesh_dispatch_requested',  'ref' => $event->request->mesh_job_reference],
                $event instanceof MeshDispatchCompleted  => ['type' => 'mesh_dispatch_completed',  'ref' => $event->request->mesh_job_reference],
                $event instanceof MeshTrustChanged       => ['type' => 'mesh_trust_changed',       'ref' => $event->node->node_id],
                default                                  => null,
            };

            if ($entry === null) {
                return;
            }

            /** @var \App\Services\TimeGraph\ExecutionTimeGraphService $graph */
            $graph = app(\App\Services\TimeGraph\ExecutionTimeGraphService::class);
            $graph->record($entry['type'], $entry['ref'], ['event' => get_class($event)]);
        } catch (\Throwable $e) {
            Log::warning('RecordMeshEventOnTimeGraph: failed to record entry.', [
                'event' => get_class($event),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
