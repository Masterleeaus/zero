<?php

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobDispatchFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly ServiceJob $job) {}
}
