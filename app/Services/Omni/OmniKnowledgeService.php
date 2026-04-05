<?php

declare(strict_types=1);

namespace App\Services\Omni;

use App\Models\Omni\OmniKnowledgeArticle;
use Illuminate\Database\Eloquent\Collection;

/**
 * OmniKnowledgeService — KB article management and search for RAG.
 *
 * Provides article search (title / summary / content) scoped to a company
 * and optional agent. Search results feed into KnowledgeManager for
 * AI retrieval context (RAG).
 */
class OmniKnowledgeService
{
    /**
     * Full-text keyword search across KB articles for a company.
     *
     * @return Collection<int, OmniKnowledgeArticle>
     */
    public function search(int $companyId, ?int $agentId, string $query, int $limit = 10): Collection
    {
        return OmniKnowledgeArticle::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->when($agentId, fn ($q) => $q->where(function ($inner) use ($agentId) {
                $inner->whereNull('agent_id')->orWhere('agent_id', $agentId);
            }))
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('summary', 'like', '%' . $query . '%')
                  ->orWhere('content', 'like', '%' . $query . '%');
            })
            ->limit($limit)
            ->get();
    }

    /**
     * Create or update a KB article.
     *
     * @param  array<string, mixed>  $data
     */
    public function upsert(int $companyId, array $data): OmniKnowledgeArticle
    {
        if (! empty($data['id'])) {
            $article = OmniKnowledgeArticle::withoutGlobalScope('company')
                ->where('company_id', $companyId)
                ->findOrFail($data['id']);
            $article->update($data);
            return $article->fresh();
        }

        return OmniKnowledgeArticle::create(array_merge($data, ['company_id' => $companyId]));
    }

    /**
     * List all active articles for a company, ordered by title.
     *
     * @return Collection<int, OmniKnowledgeArticle>
     */
    public function listActive(int $companyId, ?int $agentId = null): Collection
    {
        return OmniKnowledgeArticle::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->when($agentId, fn ($q) => $q->where(function ($inner) use ($agentId) {
                $inner->whereNull('agent_id')->orWhere('agent_id', $agentId);
            }))
            ->orderBy('title')
            ->get();
    }

    /**
     * Archive (soft-disable) a KB article.
     */
    public function archive(int $companyId, int $articleId): OmniKnowledgeArticle
    {
        $article = OmniKnowledgeArticle::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->findOrFail($articleId);

        $article->update(['status' => 'archived']);
        return $article->fresh();
    }
}
