<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\JobStage;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobStageChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly ?JobStage $previousStage,
        public readonly JobStage $newStage,
    ) {}
}
