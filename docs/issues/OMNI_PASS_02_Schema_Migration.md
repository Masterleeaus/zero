# TITAN OMNI — PASS 02: Schema + Migration Layer

**Labels:** `titan-omni` `pass-02` `migrations` `schema` `do-not-activate`
**Milestone:** Titan Omni Implementation
**Depends on:** Pass 01 complete

---

## Global Instruction

Deep-scan the repo first. Re-read all outputs from Pass 01 before building anything.

Use `docs/titan-omni/`, `CodeToUse/`, and existing host code. Do not duplicate host CRM/Jobs/Finance schemas. Use `company_id` as tenant boundary. Preserve audit trails and delivery history.

**Doctrine:** reuse → extend → refactor → repair → replace only if unavoidable.

---

## Goal

Implement the canonical Omni schema and additive migrations only.

---

## Read First — Docs

- `docs/titan-omni/schema-blueprint/TITAN_OMNI_SCHEMA_BLUEPRINT.md`
- `docs/titan-omni/source-maps/TITAN_OMNI_TABLE_MAP.md`
- `docs/titan-omni/conversation-graph/TITAN_OMNI_CONVERSATION_GRAPH.md`
- `docs/titan-omni/integration-surface/TITAN_OMNI_INTEGRATION_SURFACE.md`
- `docs/titan-omni/alignment/TITAN_OMNI_OWNERSHIP_FREEZE.md` (Pass 01 output)
- `docs/titan-omni/source-maps/TITAN_OMNI_HOST_INSERTION_MAP.md` (Pass 01 output)

---

## Read First — CodeToUse

- Donor migrations from `CodeToUse/MarketingBot*`
- Donor comms/message tables from `CodeToUse/comms*`
- Any thread/message/contact identity tables from `CodeToUse/TitanTalk*`
- Any utility migrations relevant to audit/event persistence

---

## Build — Additive Migrations For

- Contacts / identities / channel accounts
- Conversations / participants / intents / thread links
- Messages / deliveries / attachments
- Call sessions / call events
- Sequences / sequence steps / sequence runs
- Automation events
- Overlay bindings
- Handoff rules
- Consent records

---

## Rules

- Enforce `company_id` on all tables
- Keep provider/external IDs for all channel records
- Keep audit-safe event history (append-only where applicable)
- Do NOT mutate host CRM/Jobs/Finance schemas except safe FK linking if absolutely needed
- No destructive renames
- Every migration must use `Schema::hasTable()` / `Schema::hasColumn()` guards
- `up()` and `down()` must both be safe to run multiple times

---

## Required Output

- Migration files in `database/migrations/`
- `docs/titan-omni/migrations/TITAN_OMNI_MIGRATION_IMPLEMENTATION_REPORT.md`
  - List every table created
  - Note every donor source reused
  - Note any host schema touched and why

---

## Pass Delivery Rules

Output must include:
1. What was scanned (docs + CodeToUse + host)
2. What donor migration code was reused
3. What host code was extended
4. What migration files were created
5. What docs were updated
6. What remains for Pass 03
