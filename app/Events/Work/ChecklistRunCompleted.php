<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ChecklistRun;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ChecklistRun reaches completed status.
 *
 * Stage C/I — ChecklistRun lifecycle signal.
 */
class ChecklistRunCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ChecklistRun $checklistRun) {}
}
