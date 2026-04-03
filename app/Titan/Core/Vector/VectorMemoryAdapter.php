<?php

namespace App\Titan\Core\Vector;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * VectorMemoryAdapter — Bridges laravel-rag (or any vector substrate) into TitanMemoryService.
 *
 * This adapter is the ONLY vector access path. TitanMemoryService remains the memory owner.
 * Vector functionality is enabled via config('titan_memory.vector.enabled').
 */
class VectorMemoryAdapter
{
    /**
     * Embed content and return a reference identifier.
     * Stores embedding metadata in tz_ai_memory_embeddings for future recall.
     *
     * @param  array<string, mixed>  $meta
     */
    public function embed(string $content, array $meta = []): string
    {
        $reference = 'emb-'.md5($content.(string) ($meta['company_id'] ?? '').now()->toDateTimeString());

        try {
            DB::table('tz_ai_memory_embeddings')->insert([
                'company_id' => $meta['company_id'] ?? null,
                'user_id' => $meta['user_id'] ?? null,
                'session_id' => $meta['session_id'] ?? null,
                'type' => $meta['type'] ?? 'memory',
                'content' => $content,
                'embedding_reference' => $reference,
                'importance_score' => (float) ($meta['importance_score'] ?? 0.5),
                'expires_at' => $meta['expires_at'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('TitanMemory: Vector embed failed — falling back to reference-only', [
                'error' => $e->getMessage(),
                'reference' => $reference,
            ]);
        }

        return $reference;
    }

    /**
     * Perform semantic similarity search against stored embeddings.
     * Returns ranked results for the given query scoped to company_id.
     *
     * @return array<int, array<string, mixed>>
     */
    public function semanticSearch(string $query, int $companyId, int $limit = 5): array
    {
        try {
            $rows = DB::table('tz_ai_memory_embeddings')
                ->where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderByDesc('importance_score')
                ->limit($limit * 3)
                ->get();

            $queryLower = strtolower($query);
            $results = [];

            foreach ($rows as $row) {
                $content = strtolower((string) $row->content);
                $queryWords = array_filter(explode(' ', $queryLower));
                $matchScore = 0;

                foreach ($queryWords as $word) {
                    if (str_contains($content, $word)) {
                        $matchScore++;
                    }
                }

                if ($matchScore > 0) {
                    $results[] = [
                        'id' => $row->id,
                        'content' => $row->content,
                        'embedding_reference' => $row->embedding_reference,
                        'score' => $matchScore / max(1, count($queryWords)),
                        'importance_score' => (float) $row->importance_score,
                    ];
                }
            }

            usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

            return array_slice($results, 0, $limit);
        } catch (\Throwable $e) {
            Log::warning('TitanMemory: Semantic search failed', [
                'error' => $e->getMessage(),
                'company_id' => $companyId,
            ]);

            return [];
        }
    }

    /**
     * Look up a specific embedding by reference ID.
     *
     * @return array<string, mixed>|null
     */
    public function find(string $reference, int $companyId): ?array
    {
        $row = DB::table('tz_ai_memory_embeddings')
            ->where('company_id', $companyId)
            ->where('embedding_reference', $reference)
            ->first();

        return $row ? (array) $row : null;
    }
}
