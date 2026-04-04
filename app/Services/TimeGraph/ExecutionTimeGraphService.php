<?php

declare(strict_types=1);

namespace App\Services\TimeGraph;

use App\Events\TimeGraph\ExecutionAnomalyDetected;
use App\Events\TimeGraph\ExecutionCheckpointCreated;
use App\Events\TimeGraph\ExecutionGraphCompleted;
use App\Events\TimeGraph\ExecutionGraphOpened;
use App\Models\TimeGraph\ExecutionEvent;
use App\Models\TimeGraph\ExecutionGraph;
use App\Models\TimeGraph\ExecutionGraphCheckpoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExecutionTimeGraphService
{
    /** Flag to prevent infinite loop when recording graph events */
    private bool $recording = false;

    public function openGraph(Model $rootSubject, string $title): ExecutionGraph
    {
        $graph = ExecutionGraph::query()->withoutGlobalScope('company')->create([
            'company_id'        => $rootSubject->company_id ?? 0,
            'graph_id'          => Str::uuid()->toString(),
            'root_subject_type' => get_class($rootSubject),
            'root_subject_id'   => $rootSubject->getKey(),
            'title'             => $title,
            'status'            => 'active',
            'started_at'        => now(),
            'event_count'       => 0,
        ]);

        ExecutionGraphOpened::dispatch($graph);

        return $graph;
    }

    public function record(
        string $graphId,
        string $eventClass,
        Model $subject,
        array $payload,
        string $eventType,
        string $actorType = 'system',
        ?int $actorId = null,
        ?int $parentEventId = null
    ): ExecutionEvent {
        // Prevent recursive recording
        if ($this->recording) {
            return new ExecutionEvent();
        }

        $this->recording = true;

        try {
            return DB::transaction(function () use (
                $graphId, $eventClass, $subject, $payload,
                $eventType, $actorType, $actorId, $parentEventId
            ) {
                $sequence = DB::selectOne(
                    'SELECT COALESCE(MAX(sequence), 0) + 1 AS next_seq FROM execution_events WHERE graph_id = ?',
                    [$graphId]
                )->next_seq ?? 1;

                $event = ExecutionEvent::query()->withoutGlobalScope('company')->create([
                    'company_id'      => $subject->company_id ?? 0,
                    'graph_id'        => $graphId,
                    'parent_event_id' => $parentEventId,
                    'subject_type'    => get_class($subject),
                    'subject_id'      => $subject->getKey(),
                    'event_class'     => $eventClass,
                    'event_type'      => $eventType,
                    'actor_type'      => $actorType,
                    'actor_id'        => $actorId,
                    'payload'         => $payload,
                    'occurred_at'     => now(),
                    'sequence'        => $sequence,
                    'created_at'      => now(),
                ]);

                DB::table('execution_graphs')
                    ->where('graph_id', $graphId)
                    ->increment('event_count');

                return $event;
            });
        } finally {
            $this->recording = false;
        }
    }

    public function closeGraph(string $graphId): ExecutionGraph
    {
        $graph = ExecutionGraph::query()->withoutGlobalScope('company')->where('graph_id', $graphId)->firstOrFail();
        $graph->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        ExecutionGraphCompleted::dispatch($graph);

        return $graph->refresh();
    }

    public function getTimeline(string $graphId): Collection
    {
        return ExecutionEvent::query()
            ->withoutGlobalScope('company')
            ->where('graph_id', $graphId)
            ->orderBy('sequence')
            ->get();
    }

    public function createCheckpoint(string $graphId, string $label): ExecutionGraphCheckpoint
    {
        $graph = ExecutionGraph::query()->withoutGlobalScope('company')->where('graph_id', $graphId)->firstOrFail();

        $lastEvent = ExecutionEvent::query()
            ->withoutGlobalScope('company')
            ->where('graph_id', $graphId)
            ->orderByDesc('sequence')
            ->first();

        $checkpoint = ExecutionGraphCheckpoint::query()->create([
            'execution_graph_id' => $graph->id,
            'event_id'           => $lastEvent?->id,
            'label'              => $label,
            'state_snapshot'     => [
                'graph_id'    => $graphId,
                'event_count' => $graph->event_count,
                'status'      => $graph->status,
                'snapshot_at' => now()->toIso8601String(),
            ],
            'created_at'         => now(),
        ]);

        ExecutionCheckpointCreated::dispatch($checkpoint);

        return $checkpoint;
    }

    public function findCausalChain(ExecutionEvent $event): Collection
    {
        $chain = collect();
        $current = $event;

        while ($current !== null) {
            $chain->prepend($current);
            $current = $current->parent_event_id
                ? ExecutionEvent::query()->withoutGlobalScope('company')->find($current->parent_event_id)
                : null;
        }

        return $chain;
    }

    public function flagAnomalies(string $graphId): void
    {
        $graph = ExecutionGraph::query()->withoutGlobalScope('company')->where('graph_id', $graphId)->firstOrFail();
        $anomalies = app(ExecutionReplayService::class)->identifyAnomalies($graph);

        if (! empty($anomalies)) {
            ExecutionAnomalyDetected::dispatch($graph, $anomalies);
        }
    }
}
