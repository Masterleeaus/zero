# TITAN MEMORY SCHEMA

## Overview

All memory tables use the canonical `tz_` prefix and enforce `company_id` tenancy.

---

## Tables

### tz_ai_memories

Primary memory record store.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | Auto-increment |
| company_id | unsignedBigInt | Tenant boundary (indexed) |
| user_id | unsignedBigInt (nullable) | Actor identity |
| session_id | string(160) | Session scope |
| type | string(80) | `ai_decision`, `general`, etc. |
| content | text | Memory content |
| embedding_reference | string(160) (nullable) | Reference to tz_ai_memory_embeddings |
| importance_score | decimal(5,4) | 0.0000–1.0000, default 0.5 |
| expires_at | timestamp (nullable) | TTL for ephemeral memories |
| deleted_at | timestamp (nullable) | Soft-delete |
| created_at / updated_at | timestamps | |

**Indexes:** `(company_id, session_id)`, `(company_id, type)`, `(company_id, importance_score)`, `(company_id, expires_at)`

---

### tz_ai_memory_embeddings

Vector embedding storage. Used by `VectorMemoryAdapter` only.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| company_id | unsignedBigInt | Tenant boundary |
| user_id | unsignedBigInt (nullable) | |
| session_id | string(160) (nullable) | |
| type | string(80) | default `memory` |
| content | text | Original content |
| embedding_reference | string(160) UNIQUE | Embedding lookup key |
| importance_score | decimal(5,4) | |
| expires_at | timestamp (nullable) | |
| deleted_at | timestamp (nullable) | |
| created_at / updated_at | timestamps | |

**Indexes:** `(company_id, session_id)`, `(company_id, type)`, `embedding_reference`

---

### tz_ai_memory_snapshots

Rewind-compatible memory checkpoints created by `TitanMemoryService::snapshot()`.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| company_id | unsignedBigInt | Tenant boundary |
| user_id | unsignedBigInt (nullable) | |
| session_id | string(160) | |
| type | string(80) | default `session_snapshot` |
| content | longText | JSON-encoded snapshot payload |
| embedding_reference | string(160) (nullable) | |
| importance_score | decimal(5,4) | default 1.0 |
| rewind_ref | string(160) (nullable) | Links to tz_rewind_snapshots |
| expires_at | timestamp (nullable) | |
| created_at / updated_at | timestamps | |

**Indexes:** `(company_id, session_id)`, `(company_id, rewind_ref)`, `(company_id, type)`

---

### tz_ai_session_handoffs

Session handoff records created by `TitanMemoryService::storeHandoff()`.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| company_id | unsignedBigInt | Tenant boundary |
| user_id | unsignedBigInt (nullable) | |
| session_id | string(160) | |
| type | string(80) | default `handoff` |
| content | longText | JSON-encoded handoff payload |
| embedding_reference | string(160) (nullable) | |
| importance_score | decimal(5,4) | default 0.8 |
| expires_at | timestamp (nullable) | TTL: 24h default |
| created_at / updated_at | timestamps | |

**Indexes:** `(company_id, session_id)`, `(company_id, type)`, `(company_id, expires_at)`

---

## Doctrine Compliance

- All tables use `tz_` prefix
- `company_id` is the tenant boundary on every table
- No CRM / Work / Money schema overlap
- Soft-delete via `deleted_at` (tz_ai_memories, tz_ai_memory_embeddings)
- TTL via `expires_at` (all tables)

See: `docs/titancore/24_TZ_SCHEMA_PREFIX_DOCTRINE.md`
