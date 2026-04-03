<?php

namespace App\Titan\Core;

use App\Titan\Core\Vector\VectorMemoryAdapter;
use App\Titan\Signals\AuditTrail;
use App\TitanCore\Zero\Knowledge\KnowledgeManager;
use App\TitanCore\Zero\Knowledge\KnowledgeScopeResolver;
use App\TitanCore\Zero\Memory\MemoryManager;
use App\TitanCore\Zero\Memory\MemorySnapshot;
use App\TitanCore\Zero\Memory\Session\SessionHandoffManager;
use App\TitanCore\Zero\Rewind\RewindManager;
use Illuminate\Support\Facades\DB;

/**
 * TitanMemoryService — Canonical memory entrypoint for TitanCore.
 *
 * Responsibilities: store, recall, forget, summarize, snapshot, hydrateContext.
 * Always scoped by company_id. Integrates with Signal + Rewind. MCP-safe.
 */
class TitanMemoryService
{
    public function __construct(
        protected MemoryManager $memoryManager,
        protected SessionHandoffManager $sessionHandoff,
        protected KnowledgeManager $knowledgeManager,
        protected KnowledgeScopeResolver $scopeResolver,
        protected RewindManager $rewindManager,
        protected VectorMemoryAdapter $vectorAdapter,
        protected AuditTrail $auditTrail,
    ) {
    }

    /**
     * Store a memory record scoped to the tenant.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function store(int $companyId, int $userId, string $sessionId, string $type, string $content, array $context = []): array
    {
        $importance = (float) ($context['importance_score'] ?? 0.5);
        $expiresAt = $context['expires_at'] ?? null;
        $embeddingRef = null;

        if (config('titan_memory.vector.enabled', false)) {
            $embeddingRef = $this->vectorAdapter->embed($content, [
                'company_id' => $companyId,
                'session_id' => $sessionId,
                'type' => $type,
            ]);
        }

        $id = DB::table('tz_ai_memories')->insertGetId([
            'company_id' => $companyId,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'type' => $type,
            'content' => $content,
            'embedding_reference' => $embeddingRef,
            'importance_score' => $importance,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->auditTrail->recordEntry(
            $sessionId,
            'titan.memory.stored',
            ['memory_id' => $id, 'company_id' => $companyId, 'type' => $type]
        );

        return [
            'memory_id' => $id,
            'company_id' => $companyId,
            'session_id' => $sessionId,
            'type' => $type,
            'embedding_reference' => $embeddingRef,
            'stored_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Recall memories for a session, optionally performing semantic vector search.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function recall(int $companyId, string $sessionId, array $options = []): array
    {
        $query = DB::table('tz_ai_memories')
            ->where('company_id', $companyId)
            ->where('session_id', $sessionId)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('importance_score')
            ->orderByDesc('created_at')
            ->limit((int) ($options['limit'] ?? 20));

        if (isset($options['type'])) {
            $query->where('type', $options['type']);
        }

        $memories = $query->get()->map(fn ($row) => (array) $row)->toArray();

        $semanticResults = [];
        if (config('titan_memory.vector.enabled', false) && isset($options['query'])) {
            $semanticResults = $this->vectorAdapter->semanticSearch(
                (string) $options['query'],
                $companyId,
                (int) ($options['semantic_limit'] ?? 5)
            );
        }

        return [
            'company_id' => $companyId,
            'session_id' => $sessionId,
            'memories' => $memories,
            'semantic_results' => $semanticResults,
            'recalled_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Soft-delete a memory record (respect audit trail).
     *
     * @return array<string, mixed>
     */
    public function forget(int $companyId, int $memoryId): array
    {
        $updated = DB::table('tz_ai_memories')
            ->where('company_id', $companyId)
            ->where('id', $memoryId)
            ->update(['deleted_at' => now(), 'updated_at' => now()]);

        $this->auditTrail->recordEntry(
            (string) $memoryId,
            'titan.memory.forgotten',
            ['memory_id' => $memoryId, 'company_id' => $companyId]
        );

        return [
            'memory_id' => $memoryId,
            'company_id' => $companyId,
            'forgotten' => $updated > 0,
            'forgotten_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Summarize memories for a session into a distilled context string.
     *
     * @return array<string, mixed>
     */
    public function summarize(int $companyId, string $sessionId): array
    {
        $memories = DB::table('tz_ai_memories')
            ->where('company_id', $companyId)
            ->where('session_id', $sessionId)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('importance_score')
            ->limit(50)
            ->pluck('content')
            ->toArray();

        $summary = implode(' | ', array_slice($memories, 0, 10));

        return [
            'company_id' => $companyId,
            'session_id' => $sessionId,
            'summary' => $summary,
            'memory_count' => count($memories),
            'summarized_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Create a rewind-compatible memory snapshot for a session.
     *
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public function snapshot(int $companyId, string $sessionId, array $meta = []): array
    {
        $recall = $this->recall($companyId, $sessionId);
        $handoff = $this->sessionHandoff->export($sessionId, $recall);

        $snapshotKey = 'mem-snap-'.md5($companyId.$sessionId.now()->toDateTimeString());
        $rewindRef = $meta['rewind_ref'] ?? null;

        $id = DB::table('tz_ai_memory_snapshots')->insertGetId([
            'company_id' => $companyId,
            'user_id' => $meta['user_id'] ?? null,
            'session_id' => $sessionId,
            'type' => 'session_snapshot',
            'content' => json_encode($handoff, JSON_UNESCAPED_UNICODE),
            'embedding_reference' => null,
            'importance_score' => 1.0,
            'rewind_ref' => $rewindRef,
            'expires_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $legacySnapshot = (new MemorySnapshot(
            $snapshotKey,
            [
                'session' => $handoff,
                'memories' => $recall['memories'],
                'scope' => 'tenant',
                'rewind_ref' => $rewindRef,
            ],
            'tenant'
        ))->toArray();

        $this->auditTrail->recordEntry(
            $sessionId,
            'titan.memory.snapshot',
            ['snapshot_id' => $id, 'company_id' => $companyId, 'rewind_ref' => $rewindRef]
        );

        return array_merge($legacySnapshot, [
            'snapshot_id' => $id,
            'snapshot_key' => $snapshotKey,
            'rewind_ref' => $rewindRef,
            'snapshotted_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Hydrate a full AI context package from memory, knowledge and session state.
     *
     * @param  array<string, mixed>  $envelope
     * @return array<string, mixed>
     */
    public function hydrateContext(array $envelope): array
    {
        $companyId = (int) ($envelope['company_id'] ?? 0);
        $sessionId = (string) ($envelope['session_id'] ?? $envelope['id'] ?? 'global');

        $recall = $this->recall($companyId, $sessionId, [
            'query' => $envelope['input'] ?? null,
            'limit' => 10,
        ]);

        $knowledgeScope = $this->scopeResolver->scope($envelope);
        $knowledge = $this->knowledgeManager->resolve($envelope);

        return [
            'memory' => $recall,
            'knowledge' => $knowledge,
            'scope' => $knowledgeScope,
            'session_id' => $sessionId,
            'company_id' => $companyId,
            'hydrated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Store a session handoff record.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function storeHandoff(int $companyId, int $userId, string $sessionId, array $context = []): array
    {
        $exportedSession = $this->sessionHandoff->export($sessionId, $context);

        $id = DB::table('tz_ai_session_handoffs')->insertGetId([
            'company_id' => $companyId,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'type' => 'handoff',
            'content' => json_encode($exportedSession, JSON_UNESCAPED_UNICODE),
            'embedding_reference' => null,
            'importance_score' => 0.8,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'handoff_id' => $id,
            'company_id' => $companyId,
            'session_id' => $sessionId,
            'exported_session' => $exportedSession,
        ];
    }
}
