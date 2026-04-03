<?php

declare(strict_types=1);

namespace App\Events\Team;

use App\Models\User;
use App\Models\Work\JobType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CapabilityGapDetected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly ?JobType $jobType,
        public readonly array $missing,
        public readonly array $expired,
    ) {}
}
