<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\DispatchAssignment;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DispatchETAChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly DispatchAssignment $assignment,
        public readonly ?int $newTravelEstimateMins,
    ) {}
}
