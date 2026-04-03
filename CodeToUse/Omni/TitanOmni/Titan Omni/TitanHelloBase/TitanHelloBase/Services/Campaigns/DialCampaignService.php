<?php

namespace Modules\TitanHello\Services\Campaigns;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\TitanHello\Models\DialCampaign;
use Modules\TitanHello\Models\DialCampaignContact;
use Modules\TitanHello\Services\Calls\OutboundCallService;

class DialCampaignService
{
    public function __construct(
        protected OutboundCallService $outbound
    ) {}

    /**
     * Run a small batch synchronously (safe default). Later: dispatch a queued job.
     */
    public function runOneBatch(DialCampaign $campaign, Authenticatable $user, int $limit = 10): int
    {
        if (!$campaign->enabled || $campaign->status !== 'running') {
            return 0;
        }

        $contacts = DialCampaignContact::query()
            ->where('campaign_id', $campaign->id)
            ->whereIn('status', ['pending', 'failed'])
            ->where('attempt_count', '<', $campaign->max_attempts)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $count = 0;

        foreach ($contacts as $c) {
            $this->outbound->dialNumber(
                companyId: (int)$campaign->company_id,
                fromNumber: $campaign->from_number,
                toNumber: $c->phone_number,
                user: $user,
                meta: [
                    'campaign_id' => $campaign->id,
                    'campaign_contact_id' => $c->id,
                    'contact_name' => $c->name,
                ]
            );

            $c->attempt_count = (int)$c->attempt_count + 1;
            $c->status = 'calling';
            $c->last_attempt_at = now();
            $c->save();

            $count++;
        }

        return $count;
    }
}
