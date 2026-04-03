<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\JobKanbanStateChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * React to kanban state transitions on a service job.
 *
 * Module 23 — fieldservice_kanban_info
 *
 * Current responsibility: log the transition for audit.
 * Extend this listener to trigger downstream automation such as
 * dispatcher alerts, CRM notes, or SLA breach escalations.
 */
class JobKanbanStateChangedListener implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(JobKanbanStateChanged $event): void
    {
        Log::info('fsm.kanban_state_changed', [
            'job_id'         => $event->job->id,
            'previous_state' => $event->previousState,
            'new_state'      => $event->newState,
            'priority'       => $event->job->priority,
            'status'         => $event->job->status,
        ]);
    }
}
