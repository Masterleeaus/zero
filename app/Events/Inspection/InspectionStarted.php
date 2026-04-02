<?php

declare(strict_types=1);

namespace App\Events\Inspection;

use App\Models\Inspection\InspectionInstance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InspectionStarted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly InspectionInstance $inspection) {}
}
