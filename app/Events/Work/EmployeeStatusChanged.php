<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\EmploymentLifecycleState;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeStatusChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly EmploymentLifecycleState $state,
        public readonly string $previousStatus,
    ) {}
}
