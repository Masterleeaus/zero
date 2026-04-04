<?php

declare(strict_types=1);

namespace App\Listeners\Docs;

use App\Events\Work\JobStageChanged;
use App\Models\Work\JobStage;
use App\Services\Docs\DocsExecutionBridgeService;
use Illuminate\Validation\ValidationException;

/**
 * Blocks job stage transitions to a completed (closed) stage when the job
 * has mandatory injected documents that have not yet been acknowledged.
 *
 * Runs synchronously before the stage change is persisted.
 */
class BlockJobCompletionIfMandatoryUnacknowledged
{
    public function __construct(
        private readonly DocsExecutionBridgeService $bridge,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(JobStageChanged $event): void
    {
        $newStage = $event->newStage;

        // Only block transitions that are moving into a closed (completed) stage
        if (! ($newStage instanceof JobStage) || ! $newStage->is_closed) {
            return;
        }

        $unacknowledged = $this->bridge->getMandatoryUnacknowledged($event->job);

        if ($unacknowledged->isEmpty()) {
            return;
        }

        $titles = $unacknowledged
            ->map(static fn ($pivot) => $pivot->document?->title ?? 'Document #' . $pivot->document_id)
            ->implode(', ');

        throw ValidationException::withMessages([
            'mandatory_documents' => __(
                'The following mandatory documents must be acknowledged before completing this job: :titles',
                ['titles' => $titles],
            ),
        ]);
    }
}
