# MODULE 08 — DocsExecutionBridge: Procedure Injection Engine

**Label:** `titan-module` `documents` `procedures` `injection` `compliance`
**Milestone:** TitanZero Capability Expansion Pack v1
**Priority:** Medium

---

## Overview

Build the **DocsExecutionBridge** — a procedure injection engine that automatically injects relevant procedure documents, safety instructions, compliance checklists, and regulatory requirements into service jobs and inspections at the point of execution. Field technicians see the right documentation at the right time, contextually surfaced by job type, asset type, premises access level, and certification requirements.

DocsExecutionBridge connects the existing `FacilityDocument` model (already in `app/Models/Premises/FacilityDocument.php`) and job/inspection execution flow, with AI-assisted document relevance scoring via the Entity driver abstraction.

---

## Pre-Scan Requirement (MANDATORY — run before any implementation pass)

1. Read `app/Models/Premises/FacilityDocument.php` — understand existing document model fully
2. Read `app/Models/Work/ServiceJob.php` and `JobType.php` — job context for document matching
3. Read `app/Models/Inspection/InspectionTemplate.php` and `InspectionInstance.php` — inspection context
4. Read `app/Models/Premises/Premises.php` and `Unit.php` — premises context (access_level, service_priority)
5. Read `app/Models/Facility/SiteAsset.php` — asset type for procedure matching
6. Read `app/Domains/Entity/Drivers/` — AI driver interface for relevance scoring
7. Read `database/migrations/2026_04_02_000100_enhance_premises_domain.php` — premises fields added
8. Read `docs/nexuscore/` — scan for document management, procedure, or compliance injection docs
9. Read `docs/titancore/` — scan for documentation bridge or knowledge injection architecture
10. Read `CodeToUse/compliance-auditing/` and `CodeToUse/managed-premises/` — scan ALL files for document/procedure entities

---

## Canonical Models to Extend / Reference

- `app/Models/Premises/FacilityDocument.php` — primary document model
- `app/Models/Work/ServiceJob.php`
- `app/Models/Work/JobType.php`
- `app/Models/Inspection/InspectionInstance.php`
- `app/Models/Facility/SiteAsset.php`
- `app/Models/Premises/Premises.php`

---

## Required Output Artifacts

### Migrations
- `database/migrations/YYYY_MM_DD_create_docs_execution_bridge_tables.php`
  - Extend `facility_documents` table (ALTER, use `hasColumn` guards):
    - `document_category` (string 60 nullable — procedure|safety|compliance|regulatory|handover|sop|msds|permit)
    - `applies_to_asset_types` (json nullable — array of asset_type values)
    - `applies_to_job_types` (json nullable — array of job_type slugs)
    - `applies_to_service_types` (json nullable)
    - `access_level_minimum` (string 20 nullable — public|standard|restricted|secure)
    - `requires_certification` (string nullable — certification name that must be held to view)
    - `is_mandatory` (boolean default false — auto-injected without AI scoring)
    - `version` (string 20 nullable)
    - `supersedes_id` (nullable FK self-ref)
    - `review_due_at` (date nullable)
    - `embedding_vector` (json nullable — for semantic search)
  - `job_injected_documents` — documents injected into a job: `job_id`, `document_id`, `injection_source` (rule|ai_relevance|manual), `relevance_score` (decimal 5,4 nullable), `injected_at`, `acknowledged_by` (user_id nullable), `acknowledged_at`, `is_mandatory`
  - `inspection_injected_documents` — documents injected into an inspection: `inspection_instance_id`, `document_id`, `injection_source`, `relevance_score`, `injected_at`, `acknowledged_by`, `acknowledged_at`, `is_mandatory`
  - `document_injection_rules` — rule-based auto-injection config: `company_id`, `rule_type` (job_type|asset_type|service_type|access_level|premises_zone), `rule_value` (string), `document_id`, `is_mandatory`, `is_active`
- Use `Schema::hasTable()` / `Schema::hasColumn()` guards

### Models
- `app/Models/Premises/JobInjectedDocument.php` — with `BelongsTo(ServiceJob)`, `BelongsTo(FacilityDocument)`
- `app/Models/Premises/InspectionInjectedDocument.php`
- `app/Models/Premises/DocumentInjectionRule.php` — with `BelongsToCompany`
- Extend `app/Models/Premises/FacilityDocument.php` — add new relationships:
  - `injectedJobs(): HasMany(JobInjectedDocument)`
  - `injectionRules(): HasMany(DocumentInjectionRule)`
  - `supersededBy(): HasMany` (self-ref)
  - `supersedes(): BelongsTo` (self-ref)

### Services
- `app/Services/Docs/DocsExecutionBridgeService.php`
  - `injectForJob(ServiceJob $job): Collection` — returns injected documents
  - `injectForInspection(InspectionInstance $inspection): Collection`
  - `applyRuleBasedInjection(Model $context): Collection` — matches `document_injection_rules`
  - `applyAIRelevanceScoring(Model $context, Collection $candidates): Collection` — scores via Entity driver
  - `acknowledgeDocument(int $documentId, int $contextId, string $contextType, User $user): void`
  - `getMandatoryUnacknowledged(Model $context): Collection` — gates job completion
- `app/Services/Docs/DocumentSearchService.php`
  - `semanticSearch(string $query, int $companyId, array $filters = []): Collection`
  - `generateEmbedding(FacilityDocument $document): array` — via Entity driver
  - `refreshEmbeddings(int $companyId): void` — batch regeneration command
- `app/Services/Docs/DocumentVersionService.php`
  - `publishNewVersion(FacilityDocument $document, array $data): FacilityDocument`
  - `getVersionHistory(FacilityDocument $document): Collection`
  - `getActiveVersion(int $originalDocId): FacilityDocument`

### Events
- `app/Events/Docs/DocumentsInjectedForJob.php`
- `app/Events/Docs/MandatoryDocumentAcknowledged.php`
- `app/Events/Docs/DocumentVersionPublished.php`
- `app/Events/Docs/DocumentReviewDue.php`

### Listeners
- `app/Listeners/Docs/InjectDocumentsOnJobCreated.php` — fires on `ServiceJobCreated` (or equivalent)
- `app/Listeners/Docs/InjectDocumentsOnInspectionScheduled.php` — fires on `InspectionScheduled`
- `app/Listeners/Docs/BlockJobCompletionIfMandatoryUnacknowledged.php` — fires on job completion attempt

### Signals
- Emit via `SignalDispatcher`: `docs.injected`, `docs.mandatory_acknowledged`, `docs.injection_blocked`, `docs.version_published`

### Controllers / Routes
- `app/Http/Controllers/Docs/DocsExecutionBridgeController.php`
  - `forJob(ServiceJob $job)` — list injected documents for job
  - `forInspection(InspectionInstance $inspection)`
  - `acknowledge(Request $request, int $documentId)`
  - `search(Request $request)` — semantic document search
  - `rules(Request $request)` — manage injection rules
- Register in `routes/core/` as new `docs.php` route file

### Tests
- `tests/Unit/Services/Docs/DocsExecutionBridgeServiceTest.php`
- `tests/Unit/Services/Docs/DocumentSearchServiceTest.php`
- `tests/Feature/Docs/DocsExecutionBridgeControllerTest.php`

### Docs Report
- `docs/modules/MODULE_08_DocsExecutionBridge_report.md` — injection rule schema, AI relevance scoring methodology, embedding strategy, mandatory document gate design

### FSM Update
- Update `fsm_module_status.json` — set `docs_execution_bridge` to `installed`

---

## Architecture Notes

- Mandatory document acknowledgement MUST gate job completion — `BlockJobCompletionIfMandatoryUnacknowledged` must run before `JobStageService` allows transition to `completed`
- AI relevance scoring uses Entity driver (Anthropic recommended) — pass job context + document metadata, receive relevance score
- Semantic search embeddings stored as JSON — for production, consider pgvector extension; for MVP, cosine similarity in PHP is acceptable
- `requires_certification` field cross-references `CapabilityRegistry` (Module 02) — a technician without the cert should not see `restricted` documents
- Rule-based injection is synchronous; AI scoring is queued (async) — inject rule-based docs immediately, AI-scored docs arrive within seconds
- Document versioning creates a new record with `supersedes_id` pointing to the old one — active version is always the latest non-superseded
- `review_due_at` triggers a notification via `DocumentReviewDue` event — scheduled command checks for overdue reviews daily
- Respect `company_id` scoping — documents, rules, and injections are all company-scoped

---

## References

- `app/Models/Premises/FacilityDocument.php`
- `app/Models/Work/ServiceJob.php`, `JobType.php`
- `app/Models/Inspection/InspectionInstance.php`, `InspectionTemplate.php`
- `app/Models/Facility/SiteAsset.php`
- `app/Models/Premises/Premises.php`
- `app/Domains/Entity/Drivers/` (AI driver interface)
- `app/Services/Work/JobStageService.php` (completion gate integration)
- `app/Titan/Signals/SignalDispatcher.php`
- `database/migrations/2026_04_02_000100_enhance_premises_domain.php`
- `CodeToUse/compliance-auditing/` (full scan)
- `CodeToUse/managed-premises/` (full scan)
- `docs/nexuscore/` (document management, compliance docs)
- `docs/titancore/` (knowledge injection architecture)
