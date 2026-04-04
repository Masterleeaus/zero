<?php

namespace App\Services\Omni;

use App\Models\Omni\OmniConversation;
use Illuminate\Support\Collection;

class OmniConversationReadService
{
    public function recentForAgent(int $companyId, int $agentId, int $limit = 25): Collection
    {
        return OmniConversation::query()
            ->where('company_id', $companyId)
            ->where('agent_id', $agentId)
            ->with(['messages'])
            ->orderByDesc('last_activity_at')
            ->limit($limit)
            ->get();
    }
}
