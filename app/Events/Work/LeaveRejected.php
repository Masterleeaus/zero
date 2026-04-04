<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\User;
use App\Models\Work\Leave;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Leave $leave,
        public readonly User $rejector,
        public readonly string $reason,
    ) {}
}
