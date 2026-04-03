<?php

namespace App\Exceptions;

/**
 * Exception for knowledge base operations (search, indexing, retrieval).
 */
class OmniKnowledgeException extends OmniException
{
    public static function searchFailed(string $query, ?string $reason = null): self
    {
        return new self(
            "Knowledge base search failed for query: {$query}",
            0,
            null,
            503,
            'KNOWLEDGE_SEARCH_FAILED',
            ['query' => $query, 'reason' => $reason]
        );
    }

    public static function indexingFailed(int $articleId, ?string $reason = null): self
    {
        return new self(
            "Failed to index knowledge article {$articleId}",
            0,
            null,
            500,
            'KNOWLEDGE_INDEXING_FAILED',
            ['article_id' => $articleId, 'reason' => $reason]
        );
    }

    public static function invalidSource(string $sourceType, ?string $reason = null): self
    {
        return new self(
            "Invalid knowledge source type: {$sourceType}",
            0,
            null,
            400,
            'KNOWLEDGE_INVALID_SOURCE',
            ['source_type' => $sourceType, 'reason' => $reason]
        );
    }
}
