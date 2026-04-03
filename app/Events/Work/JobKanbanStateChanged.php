<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a service job's kanban_state transitions.
 *
 * Module 23 — fieldservice_kanban_info
 */
class JobKanbanStateChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly string $previousState,
        public readonly string $newState,
    ) {}
}
