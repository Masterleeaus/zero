# TITAN MEMORY VECTOR LAYER

## Overview

The vector substrate is provided by `VectorMemoryAdapter`, which bridges
laravel-rag capabilities into `TitanMemoryService`.

**Ownership rule:** `TitanMemoryService` owns all memory. The vector layer is
substrate only — it stores and retrieves embeddings but never creates or
manages memories independently.

---

## Adapter Location

```
app/Titan/Core/Vector/VectorMemoryAdapter.php
```

Namespace: `App\Titan\Core\Vector`

---

## Capabilities

| Method | Purpose |
|---|---|
| `embed($content, $meta)` | Embed content and store reference in `tz_ai_memory_embeddings` |
| `semanticSearch($query, $companyId, $limit)` | Keyword-based approximate semantic search |
| `find($reference, $companyId)` | Look up an embedding by reference ID |

---

## Activation

Vector recall is disabled by default. Enable via:

```env
TITAN_MEMORY_VECTOR_ENABLED=true
```

Or in `config/titan_memory.php`:

```php
'vector' => [
    'enabled' => true,
    'max_semantic_results' => 5,
    'embedding_model' => 'text-embedding-3-small',
    'dimensions' => 1536,
],
```

---

## Current Implementation

The current adapter uses a keyword-matching strategy for semantic search
(no external API required). This is a provider-agnostic substrate that can
be upgraded to use full vector embeddings (pgvector, OpenAI embeddings) by
replacing the `embed()` and `semanticSearch()` implementations.

The `tz_ai_memory_embeddings` table is already provisioned for future
vector storage with `embedding_reference` as the lookup key.

---

## laravel-rag Source

The laravel-rag bundle is available at:

```
CodeToUse/aicore/AICores/laravel-rag-main/
```

Key capabilities available for future integration:
- OpenAI `text-embedding-3-small` embeddings (1536 dims)
- pgvector / sqlite-vec vector stores
- Cosine similarity search with RRF ranking
- Chunking strategies: Character, Sentence, Markdown, Semantic
- Memory token budget: 4000 tokens, 0.8 summary threshold

Integration path: Replace `VectorMemoryAdapter::embed()` with the
laravel-rag `EmbeddingService` and `VectorStore` when ready.
TitanMemoryService requires no changes — adapter is the only integration point.

---

## Non-Goals

- Vector layer must NOT become memory owner
- Vector layer must NOT bypass company_id scoping
- Vector layer must NOT write directly to tz_ai_memories
