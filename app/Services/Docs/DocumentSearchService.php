<?php

declare(strict_types=1);

namespace App\Services\Docs;

use App\Models\Premises\FacilityDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * DocumentSearchService
 *
 * Provides semantic search over facility documents using embedding vectors
 * and AI-powered relevance scoring (via the Entity/Anthropic driver when available).
 */
class DocumentSearchService
{
    /**
     * Search documents by semantic similarity to a query string.
     *
     * @param  array<string, mixed>  $filters  Optional filters: document_category, is_mandatory, status
     * @return Collection<FacilityDocument>
     */
    public function semanticSearch(string $query, int $companyId, array $filters = []): Collection
    {
        $dbQuery = FacilityDocument::query()
            ->where('company_id', $companyId)
            ->where('status', 'valid');

        if (isset($filters['document_category'])) {
            $dbQuery->where('document_category', $filters['document_category']);
        }

        if (isset($filters['is_mandatory'])) {
            $dbQuery->where('is_mandatory', (bool) $filters['is_mandatory']);
        }

        if (! empty($filters['status'])) {
            $dbQuery->where('status', $filters['status']);
        }

        $candidates = $dbQuery->get();

        if ($candidates->isEmpty() || trim($query) === '') {
            return $candidates;
        }

        // Score and rank by relevance
        return $candidates
            ->map(function (FacilityDocument $doc) use ($query) {
                $doc->_relevance_score = $this->scoreRelevance($doc, $query);
                return $doc;
            })
            ->sortByDesc('_relevance_score')
            ->values();
    }

    /**
     * Generate an embedding vector for a document.
     * Uses a simple keyword extraction fallback when no AI driver is available.
     *
     * @return array<int, float>
     */
    public function generateEmbedding(FacilityDocument $document): array
    {
        // Attempt to use the Entity/Anthropic voyage driver if configured
        try {
            $driver = $this->resolveEmbeddingDriver();

            if ($driver !== null) {
                $text      = $this->documentToText($document);
                $embedding = $driver->embed($text);

                $document->update(['embedding_vector' => $embedding]);

                return $embedding;
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentSearchService: embedding driver failed — using fallback', [
                'document_id' => $document->id,
                'error'       => $e->getMessage(),
            ]);
        }

        // Fallback: simple term-frequency vector
        $vector = $this->buildSimpleVector($document);
        $document->update(['embedding_vector' => $vector]);

        return $vector;
    }

    /**
     * Refresh embeddings for all valid documents belonging to a company.
     */
    public function refreshEmbeddings(int $companyId): void
    {
        FacilityDocument::query()
            ->where('company_id', $companyId)
            ->where('status', 'valid')
            ->each(function (FacilityDocument $doc) {
                $this->generateEmbedding($doc);
            });
    }

    /**
     * Score the relevance of a document against a context description string.
     * Returns a float between 0 and 1.
     */
    public function scoreRelevance(FacilityDocument $document, string $contextSummary): float
    {
        if (trim($contextSummary) === '') {
            return 0.0;
        }

        // If both sides have embedding vectors, use cosine similarity
        if (! empty($document->embedding_vector)) {
            $docVector     = $document->embedding_vector;
            $queryVector   = $this->buildQueryVector($contextSummary, count($docVector));

            return $this->cosineSimilarity($docVector, $queryVector);
        }

        // Fallback: keyword overlap score
        return $this->keywordOverlapScore(
            $this->documentToText($document),
            $contextSummary,
        );
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    /**
     * Attempt to resolve an embedding driver (Voyage / Anthropic).
     * Returns null if none is configured.
     */
    private function resolveEmbeddingDriver(): mixed
    {
        // Check for Voyage embedding driver (used by Anthropic / Claude context)
        try {
            if (class_exists(\App\Domains\Entity\Drivers\Anthropic\VoyageLarge2Driver::class)) {
                $driver = app(\App\Domains\Entity\Drivers\Anthropic\VoyageLarge2Driver::class);
                if (method_exists($driver, 'embed')) {
                    return $driver;
                }
            }
        } catch (\Throwable) {
            // Driver not bound — fall through
        }

        return null;
    }

    /** Flatten document fields into a single searchable string. */
    private function documentToText(FacilityDocument $document): string
    {
        return implode(' ', array_filter([
            $document->title,
            $document->document_category,
            $document->doc_type,
            $document->notes,
        ]));
    }

    /**
     * Build a very simple term-frequency vector for keyword-based fallback.
     *
     * @return array<int, float>
     */
    private function buildSimpleVector(FacilityDocument $document): array
    {
        $text   = strtolower($this->documentToText($document));
        $tokens = preg_split('/\W+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $freq = array_count_values($tokens);
        $max  = max($freq) ?: 1;

        return array_values(array_map(
            static fn (int $count): float => $count / $max,
            $freq,
        ));
    }

    /**
     * Build a query vector of the same length as the document vector.
     *
     * @param  array<int, float>  $reference
     * @return array<int, float>
     */
    private function buildQueryVector(string $query, int $length): array
    {
        $text   = strtolower($query);
        $tokens = preg_split('/\W+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $freq   = array_count_values($tokens);
        $max    = max($freq) ?: 1;
        $raw    = array_values(array_map(
            static fn (int $c): float => $c / $max,
            $freq,
        ));

        // Pad or truncate to match document vector length
        if (count($raw) < $length) {
            $raw = array_pad($raw, $length, 0.0);
        } else {
            $raw = array_slice($raw, 0, $length);
        }

        return $raw;
    }

    /**
     * Cosine similarity between two float vectors.
     *
     * @param  array<int, float>  $a
     * @param  array<int, float>  $b
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $len = min(count($a), count($b));

        if ($len === 0) {
            return 0.0;
        }

        $dot   = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $len; $i++) {
            $dot   += $a[$i] * $b[$i];
            $normA += $a[$i] ** 2;
            $normB += $b[$i] ** 2;
        }

        $denom = sqrt($normA) * sqrt($normB);

        return $denom > 0 ? min(1.0, max(0.0, $dot / $denom)) : 0.0;
    }

    /** Simple keyword overlap score as a fraction of matched unique tokens. */
    private function keywordOverlapScore(string $docText, string $queryText): float
    {
        $docTokens   = array_unique(preg_split('/\W+/', strtolower($docText), -1, PREG_SPLIT_NO_EMPTY) ?: []);
        $queryTokens = array_unique(preg_split('/\W+/', strtolower($queryText), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        if (empty($queryTokens)) {
            return 0.0;
        }

        $overlap = count(array_intersect($docTokens, $queryTokens));

        return min(1.0, $overlap / count($queryTokens));
    }
}
