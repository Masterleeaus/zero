<?php

declare(strict_types=1);

namespace App\Events\TimeGraph;

use App\Models\TimeGraph\ExecutionGraphCheckpoint;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExecutionCheckpointCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly ExecutionGraphCheckpoint $checkpoint) {}
}
