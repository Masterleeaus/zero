<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\Department;
use App\Models\Work\StaffProfile;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepartmentAssigned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly StaffProfile $staffProfile,
        public readonly Department $department,
    ) {}
}
