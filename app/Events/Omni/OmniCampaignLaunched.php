<?php

declare(strict_types=1);

namespace App\Events\Omni;

use App\Models\Omni\Campaign\OmniCampaign;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OmniCampaignLaunched
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly OmniCampaign $campaign) {}
}
