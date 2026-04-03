# TITAN OMNI — PASS 03: Models + Relationships + Tenancy

**Labels:** `titan-omni` `pass-03` `models` `tenancy` `do-not-activate`
**Milestone:** Titan Omni Implementation
**Depends on:** Pass 02 complete

---

## Global Instruction

Re-read all Pass 01 and Pass 02 outputs before building. Use `docs/titan-omni/`, `CodeToUse/`, and existing host models/traits. Do not duplicate existing model ownership. Use `company_id` as tenant boundary.

**Doctrine:** reuse → extend → refactor → repair → replace only if unavoidable.

---

## Goal

Create the model/domain layer on top of the Pass 02 schema.

---

## Read First — Docs

- `docs/titan-omni/models/TITAN_OMNI_MODEL_MAP.md`
- `docs/titan-omni/conversation-graph/TITAN_OMNI_CONVERSATION_GRAPH.md`
- `docs/titan-omni/source-maps/TITAN_OMNI_TABLE_MAP.md`
- `docs/titan-omni/alignment/TITAN_OMNI_OWNERSHIP_FREEZE.md` (Pass 01 output)
- `docs/titan-omni/migrations/TITAN_OMNI_MIGRATION_IMPLEMENTATION_REPORT.md` (Pass 02 output)

---

## Read First — CodeToUse

- Donor models from `CodeToUse/MarketingBot*`
- Any contact / identity / message models from `CodeToUse/comms*` / `CodeToUse/TitanTalk*`
- Utility traits for tenancy, audit, timestamps, status handling
- Existing host traits in `app/Models/Concerns/` — use these first

---

## Build — Omni Models

Implement models for every table created in Pass 02:
- Contact / Identity / ChannelAccount
- Conversation / Participant / Intent / ThreadLink
- Message / Delivery / Attachment
- CallSession / CallEvent
- Sequence / SequenceStep / SequenceRun
- AutomationEvent
- OverlayBinding
- HandoffRule
- ConsentRecord

For each model:
- `$fillable` array
- `$casts` (dates, booleans, JSON columns)
- `$attributes` defaults where needed
- All `BelongsTo`, `HasMany`, `MorphTo`, `MorphMany` relationships
- `BelongsToCompany` trait (or host equivalent)
- Status constants/enums where applicable
- Named query scopes (`scopeActive`, `scopePending`, `scopeForChannel`, etc.)

---

## Rules

- Prefer existing host tenancy traits (`app/Models/Concerns/BelongsToCompany.php`) if compatible
- Preserve `company_id` boundary on every model
- Avoid duplicate contact/business ownership — Omni Contact links to host Customer, does not replace it
- Keep model API clean and service-ready
- No business logic in models — scopes and accessors only

---

## Required Output

- Model files in `app/Models/Omni/`
- Shared traits in `app/Models/Omni/Concerns/` if needed
- `docs/titan-omni/models/TITAN_OMNI_MODEL_IMPLEMENTATION_REPORT.md`
  - Full model list
  - Relationship map
  - Traits used
  - Host model links (which Omni models link to which host models)

---

## Pass Delivery Rules

Output must include:
1. What was scanned
2. What donor model code was reused
3. What host traits/models were extended
4. What files were created
5. What docs were updated
6. What remains for Pass 04
