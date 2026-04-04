# MODULE 08 — DocsExecutionBridge Implementation Report

**Status:** Installed  
**Installed At:** 2026-04-04  
**Domain:** Docs / Premises / Work / Inspection

---

## Overview

DocsExecutionBridge is a procedure injection engine that automatically surfaces relevant procedure documents, safety instructions, compliance checklists, and regulatory requirements into service jobs and inspections at the point of execution.

Field technicians see the right documentation at the right time — contextually matched by job type, asset type, premises access level, and certification requirements.

---

## Artifacts Delivered

### Migration

| File | Purpose |
|------|---------|
| `database/migrations/2026_04_04_000800_create_docs_execution_bridge_tables.php` | Extends `facility_documents`; creates `job_injected_documents`, `inspection_injected_documents`, `document_injection_rules` |

**Schema additions to `facility_documents`:**
- `document_category` — procedure | safety | compliance | regulatory | handover | sop | msds | permit
- `applies_to_asset_types` (json)
- `applies_to_job_types` (json)
- `applies_to_service_types` (json)
- `access_level_minimum`
- `requires_certification`
- `is_mandatory` (boolean)
- `version`
- `supersedes_id` (self-referential FK)
- `review_due_at`
- `embedding_vector` (json)

All column additions are guarded with `Schema::hasColumn()` checks.

---

### Models

| Model | Table | Purpose |
|-------|-------|---------|
| `App\Models\Premises\JobInjectedDocument` | `job_injected_documents` | Pivot: job ↔ document injection |
| `App\Models\Premises\InspectionInjectedDocument` | `inspection_injected_documents` | Pivot: inspection ↔ document injection |
| `App\Models\Premises\DocumentInjectionRule` | `document_injection_rules` | Rule definitions for automatic injection |

**`FacilityDocument` extensions:**
- `injectedJobs()` → HasMany JobInjectedDocument
- `injectionRules()` → HasMany DocumentInjectionRule
- `supersededBy()` → HasMany FacilityDocument (self-ref)
- `supersedes()` → BelongsTo FacilityDocument (self-ref)
- `isReviewDue()`, `isSuperseded()` helpers

**`ServiceJob` extension:**
- `injectedDocuments()` → HasMany JobInjectedDocument

**`InspectionInstance` extension:**
- `injectedDocuments()` → HasMany InspectionInjectedDocument

---

### Services

| Service | Location |
|---------|----------|
| `DocsExecutionBridgeService` | `app/Services/Docs/DocsExecutionBridgeService.php` |
| `DocumentSearchService` | `app/Services/Docs/DocumentSearchService.php` |
| `DocumentVersionService` | `app/Services/Docs/DocumentVersionService.php` |

**DocsExecutionBridgeService methods:**
- `injectForJob(ServiceJob)` — synchronous rule-based injection + event dispatch
- `injectForInspection(InspectionInstance)` — synchronous rule-based injection
- `applyRuleBasedInjection(Model)` — matches DocumentInjectionRule rows against context
- `applyAIRelevanceScoring(Model, Collection)` — AI-scored injection (uses DocumentSearchService)
- `acknowledgeDocument(int, int, string, User)` — marks a pivot acknowledged
- `getMandatoryUnacknowledged(Model)` — returns unacknowledged mandatory pivots

**DocumentSearchService methods:**
- `semanticSearch(string, int, array)` — filter + rank documents by relevance
- `generateEmbedding(FacilityDocument)` — creates/updates `embedding_vector`
- `refreshEmbeddings(int)` — batch embedding refresh
- `scoreRelevance(FacilityDocument, string)` — cosine similarity or keyword overlap

**DocumentVersionService methods:**
- `publishNewVersion(FacilityDocument, array)` — creates new version, marks old as `superseded`
- `getActiveVersion(int)` — walks supersedes chain to find latest active version

---

### Events

| Event | Location |
|-------|----------|
| `DocumentsInjectedForJob` | `app/Events/Docs/DocumentsInjectedForJob.php` |
| `MandatoryDocumentAcknowledged` | `app/Events/Docs/MandatoryDocumentAcknowledged.php` |
| `DocumentVersionPublished` | `app/Events/Docs/DocumentVersionPublished.php` |
| `DocumentReviewDue` | `app/Events/Docs/DocumentReviewDue.php` |
| `JobCreated` (new Work event) | `app/Events/Work/JobCreated.php` |

---

### Listeners

| Listener | Listens To | Queue |
|----------|-----------|-------|
| `InjectDocumentsOnJobCreated` | `JobCreated` | ✅ Queued |
| `InjectDocumentsOnInspectionScheduled` | `InspectionScheduled` | ✅ Queued |
| `BlockJobCompletionIfMandatoryUnacknowledged` | `JobStageChanged` | ❌ Synchronous |

All listeners registered in `EventServiceProvider`.

---

### Controller & Routes

**Controller:** `app/Http/Controllers/Docs/DocsExecutionBridgeController.php`

| Method | Route | Name |
|--------|-------|------|
| `forJob()` | `GET /dashboard/docs/jobs/{job}` | `dashboard.docs.jobs.documents` |
| `forInspection()` | `GET /dashboard/docs/inspections/{inspection}` | `dashboard.docs.inspections.documents` |
| `acknowledge()` | `POST /dashboard/docs/acknowledge` | `dashboard.docs.acknowledge` |
| `search()` | `GET /dashboard/docs/search` | `dashboard.docs.search` |
| `rules()` | `GET /dashboard/docs/rules` | `dashboard.docs.rules.index` |

**Route file:** `routes/core/docs.routes.php`

---

### JobStageService Gate

`JobStageService::validateTransition()` now checks for unacknowledged mandatory documents before allowing transition to a closed (completed) stage:

```php
// MODULE 08 — block closure when mandatory documents are unacknowledged
if ($newStage->is_closed) {
    $unacknowledged = $docsBridge->getMandatoryUnacknowledged($job);
    if ($unacknowledged->isNotEmpty()) {
        throw ValidationException::withMessages([...]);
    }
}
```

The `BlockJobCompletionIfMandatoryUnacknowledged` listener provides a secondary enforcement point via the `JobStageChanged` event.

---

### Tests

| Test File | Type |
|-----------|------|
| `tests/Unit/Services/Docs/DocsExecutionBridgeServiceTest.php` | Unit |
| `tests/Unit/Services/Docs/DocumentSearchServiceTest.php` | Unit |
| `tests/Feature/Docs/DocsExecutionBridgeControllerTest.php` | Feature |

---

## Architecture Notes

- **Rule-based injection** is synchronous and runs on job/inspection creation
- **AI relevance scoring** is available via `applyAIRelevanceScoring()` — uses Voyage embedding driver if available, falls back to keyword overlap
- **Mandatory document acknowledgement** gates job completion at the `JobStageService` level
- **Document versioning** uses self-referential `supersedes_id` — the active version is the latest non-superseded record in the chain
- **Embedding vectors** are stored as JSON in `facility_documents.embedding_vector` — cosine similarity used when present
- **`requires_certification`** cross-references CapabilityRegistry (Module 02) via field value matching

---

## Integration Map

```
FacilityDocument ──────────────── JobInjectedDocument ──── ServiceJob
     │                            InspectionInjectedDocument ── InspectionInstance
     └── DocumentInjectionRule ──── company_id scope

DocsExecutionBridgeService ─── JobStageService (validates before close)
     │
     ├── applyRuleBasedInjection()  ← DocumentInjectionRule.rule_type/rule_value
     │
     └── applyAIRelevanceScoring()  ← DocumentSearchService (cosine / keyword)

Events: JobCreated → InjectDocumentsOnJobCreated
        InspectionScheduled → InjectDocumentsOnInspectionScheduled
        JobStageChanged → BlockJobCompletionIfMandatoryUnacknowledged
```
