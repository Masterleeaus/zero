<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ShiftAssignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShiftAssigned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ShiftAssignment $assignment) {}
}
