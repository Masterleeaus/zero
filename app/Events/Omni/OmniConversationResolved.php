<?php

declare(strict_types=1);

namespace App\Events\Omni;

use App\Models\Omni\OmniConversation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OmniConversationResolved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly OmniConversation $conversation) {}
}
