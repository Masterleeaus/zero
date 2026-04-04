<?php

declare(strict_types=1);

namespace App\Jobs\TimeGraph;

use App\Models\TimeGraph\ExecutionGraph;
use App\Services\TimeGraph\ExecutionTimeGraphService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class RecordSignalToTimeGraph implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly string $processId,
        private readonly array $payload,
    ) {}

    public function handle(ExecutionTimeGraphService $service): void
    {
        $entityType = $this->payload['entity_type'] ?? null;
        $entityId   = $this->payload['entity_id'] ?? null;

        if (! $entityType || ! $entityId) {
            return;
        }

        $subjectTypeMap = [
            'service_job' => \App\Models\Work\ServiceJob::class,
        ];

        $subjectClass = $subjectTypeMap[$entityType] ?? null;
        if (! $subjectClass || ! class_exists($subjectClass)) {
            return;
        }

        $subject = $subjectClass::find($entityId);
        if (! $subject) {
            return;
        }

        $graph = ExecutionGraph::query()
            ->withoutGlobalScope('company')
            ->where('root_subject_type', $subjectClass)
            ->where('root_subject_id', $entityId)
            ->where('company_id', $subject->company_id)
            ->where('status', 'active')
            ->first();

        if (! $graph) {
            return;
        }

        $service->record(
            graphId: $graph->graph_id,
            eventClass: 'signal.' . ($this->payload['domain'] ?? 'general'),
            subject: $subject,
            payload: $this->payload,
            eventType: 'signal_emitted',
            actorType: 'system',
            actorId: null,
        );
    }
}
