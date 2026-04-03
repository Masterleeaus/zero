<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\FSM\FsmJobBlocker;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a blocking reason is added to a service job.
 *
 * Module 23 — fieldservice_kanban_info
 */
class JobBlockerAdded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly FsmJobBlocker $blocker,
    ) {}
}
