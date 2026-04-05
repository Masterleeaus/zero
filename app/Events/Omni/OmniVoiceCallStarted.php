<?php

declare(strict_types=1);

namespace App\Events\Omni;

use App\Models\Omni\Voice\OmniVoiceCall;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OmniVoiceCallStarted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly OmniVoiceCall $call) {}
}
