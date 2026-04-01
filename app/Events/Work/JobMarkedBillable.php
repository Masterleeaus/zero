<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobMarkedBillable
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ServiceJob $job) {}
}
