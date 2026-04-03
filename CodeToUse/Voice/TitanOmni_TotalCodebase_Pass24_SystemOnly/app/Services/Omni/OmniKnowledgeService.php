<?php

namespace App\Services\Omni;

use App\Models\Omni\OmniKnowledgeArticle;
use Illuminate\Support\Collection;

class OmniKnowledgeService
{
    public function search(int $companyId, ?int $agentId, string $query): Collection
    {
        return OmniKnowledgeArticle::query()
            ->where('company_id', $companyId)
            ->when($agentId, fn ($q) => $q->where(function ($inner) use ($agentId) {
                $inner->whereNull('agent_id')->orWhere('agent_id', $agentId);
            }))
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('summary', 'like', '%' . $query . '%')
                  ->orWhere('content', 'like', '%' . $query . '%');
            })
            ->limit(10)
            ->get();
    }
}
