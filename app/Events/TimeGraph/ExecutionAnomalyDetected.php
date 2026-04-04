<?php

declare(strict_types=1);

namespace App\Events\TimeGraph;

use App\Models\TimeGraph\ExecutionGraph;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExecutionAnomalyDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ExecutionGraph $graph,
        public readonly array $anomalies
    ) {}
}
