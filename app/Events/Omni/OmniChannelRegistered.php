<?php

declare(strict_types=1);

namespace App\Events\Omni;

use App\Models\Omni\OmniChannelBridge;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OmniChannelRegistered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly OmniChannelBridge $bridge) {}
}
