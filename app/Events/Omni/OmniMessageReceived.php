<?php

declare(strict_types=1);

namespace App\Events\Omni;

use App\Models\Omni\OmniMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OmniMessageReceived
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly OmniMessage $message) {}
}
