<?php

namespace App\Events\Work;

use App\Models\Work\DispatchAssignment;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobReDispatched
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly DispatchAssignment $assignment,
        public readonly string $reason,
    ) {}
}
