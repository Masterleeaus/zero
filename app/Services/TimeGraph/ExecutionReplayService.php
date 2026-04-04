<?php

declare(strict_types=1);

namespace App\Services\TimeGraph;

use App\Models\TimeGraph\ExecutionEvent;
use App\Models\TimeGraph\ExecutionGraph;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExecutionReplayService
{
    public function buildReplayPlan(ExecutionGraph $graph, Carbon $toTime): array
    {
        $events = ExecutionEvent::query()
            ->withoutGlobalScope('company')
            ->where('graph_id', $graph->graph_id)
            ->where('occurred_at', '<=', $toTime)
            ->orderBy('sequence')
            ->get();

        return [
            'graph_id'    => $graph->graph_id,
            'title'       => $graph->title,
            'replay_to'   => $toTime->toIso8601String(),
            'event_count' => $events->count(),
            'steps'       => $events->map(fn (ExecutionEvent $e) => [
                'sequence'    => $e->sequence,
                'event_type'  => $e->event_type,
                'event_class' => $e->event_class,
                'actor_type'  => $e->actor_type,
                'actor_id'    => $e->actor_id,
                'occurred_at' => $e->occurred_at?->toIso8601String(),
                'description' => $this->describeDecision($e),
            ])->values()->all(),
        ];
    }

    public function describeDecision(ExecutionEvent $event): string
    {
        $actor = $event->actor_type === 'user'
            ? "User #{$event->actor_id}"
            : ucfirst((string) $event->actor_type);

        $subject = class_basename((string) $event->subject_type) . " #{$event->subject_id}";

        return match ($event->event_type) {
            'stage_transition'  => "{$actor} moved {$subject} through a stage transition",
            'signal_emitted'    => "{$actor} emitted signal on {$subject}",
            'user_action'       => "{$actor} performed an action on {$subject}",
            'ai_decision'       => "{$actor} made an AI decision on {$subject}",
            'system_trigger'    => "System triggered an event on {$subject}",
            'external_event'    => "External event received for {$subject}",
            'rewind_applied'    => "Rewind applied to {$subject} by {$actor}",
            'sync_received'     => "Sync event received for {$subject}",
            default             => "Event '{$event->event_type}' on {$subject}",
        };
    }

    public function exportTimeline(ExecutionGraph $graph): array
    {
        $events = ExecutionEvent::query()
            ->withoutGlobalScope('company')
            ->where('graph_id', $graph->graph_id)
            ->orderBy('sequence')
            ->get();

        return [
            'graph'  => [
                'id'                => $graph->id,
                'graph_id'          => $graph->graph_id,
                'title'             => $graph->title,
                'status'            => $graph->status,
                'root_subject_type' => $graph->root_subject_type,
                'root_subject_id'   => $graph->root_subject_id,
                'started_at'        => $graph->started_at?->toIso8601String(),
                'completed_at'      => $graph->completed_at?->toIso8601String(),
                'event_count'       => $graph->event_count,
            ],
            'events' => $events->map(fn (ExecutionEvent $e) => [
                'id'           => $e->id,
                'sequence'     => $e->sequence,
                'event_type'   => $e->event_type,
                'event_class'  => $e->event_class,
                'actor_type'   => $e->actor_type,
                'actor_id'     => $e->actor_id,
                'subject_type' => $e->subject_type,
                'subject_id'   => $e->subject_id,
                'occurred_at'  => $e->occurred_at?->toIso8601String(),
                'description'  => $this->describeDecision($e),
                'payload'      => $e->payload,
            ])->values()->all(),
        ];
    }

    public function identifyAnomalies(ExecutionGraph $graph): array
    {
        $events = ExecutionEvent::query()
            ->withoutGlobalScope('company')
            ->where('graph_id', $graph->graph_id)
            ->orderBy('sequence')
            ->get();

        if ($events->count() < 3) {
            return [];
        }

        $gaps = collect();
        $prev = null;
        foreach ($events as $event) {
            if ($prev !== null) {
                $key = $prev->event_type . '->' . $event->event_type;
                $gapSeconds = $event->occurred_at->diffInSeconds($prev->occurred_at);
                $gaps->push(['key' => $key, 'gap' => $gapSeconds, 'event_id' => $event->id]);
            }
            $prev = $event;
        }

        if ($gaps->isEmpty()) {
            return [];
        }

        $byKey = $gaps->groupBy('key');
        $anomalies = [];

        foreach ($byKey as $key => $keyGaps) {
            $sorted = $keyGaps->pluck('gap')->sort()->values();
            $median = $sorted->median();

            foreach ($keyGaps as $gap) {
                if ($median > 0 && $gap['gap'] > ($median * 2)) {
                    $anomalies[] = [
                        'event_id'       => $gap['event_id'],
                        'transition'     => $key,
                        'gap_seconds'    => $gap['gap'],
                        'median_seconds' => $median,
                        'ratio'          => round($gap['gap'] / $median, 2),
                    ];
                }
            }
        }

        return $anomalies;
    }
}
