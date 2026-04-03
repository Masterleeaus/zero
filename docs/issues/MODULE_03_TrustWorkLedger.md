# MODULE 03 — TrustWorkLedger: Evidence Chain Execution Layer

**Label:** `titan-module` `trust` `evidence` `audit` `compliance`
**Milestone:** TitanZero Capability Expansion Pack v1
**Priority:** High

---

## Overview

Build the **TrustWorkLedger** — an immutable, append-only evidence chain that records every meaningful execution event across service jobs, inspections, and asset service events. This is not a general audit log; it is a cryptographically-chained, tamper-evident ledger purpose-built for compliance, dispute resolution, and client-facing proof of work.

Every entry links to a parent hash, creating an unbreakable chain. Evidence can include: job completion signatures, photo attachments, inspection responses, checklist completions, technician sign-offs, and client acknowledgements.

TrustWorkLedger integrates with `ServiceJob`, `InspectionInstance`, `AssetServiceEvent`, `ChecklistRun`, and the full Titan Signals `AuditTrail`.

---

## Pre-Scan Requirement (MANDATORY — run before any implementation pass)

1. Read `app/Models/Work/ServiceJob.php` — all fields and relationships
2. Read `app/Models/Inspection/InspectionInstance.php` — structure and status fields
3. Read `app/Models/Facility/AssetServiceEvent.php` — structure
4. Read `app/Models/Work/ChecklistRun.php` and `ChecklistResponse.php` — completion data structure
5. Read `app/Titan/Signals/AuditTrail.php` — understand existing audit infrastructure
6. Read `app/Titan/Signals/ProcessRecorder.php` — execution recording pattern
7. Read `app/Titan/Signals/EnvelopeBuilder.php` — signal envelope structure
8. Read `database/migrations/` — all existing job, inspection, checklist table schemas
9. Read `docs/nexuscore/` — scan for trust, evidence, compliance, or ledger design docs
10. Read `docs/titancore/` — scan for TitanRewind and audit trail architecture docs

---

## Canonical Models to Extend / Reference

- `app/Models/Work/ServiceJob.php`
- `app/Models/Inspection/InspectionInstance.php`
- `app/Models/Facility/AssetServiceEvent.php`
- `app/Models/Work/ChecklistRun.php`
- `app/Titan/Signals/AuditTrail.php` — extend, do not replace
- `app/Extensions/TitanRewind/System/` — understand dependency graph before adding ledger entries

---

## Required Output Artifacts

### Migrations
- `database/migrations/YYYY_MM_DD_create_trust_ledger_tables.php`
  - `trust_ledger_entries` — the immutable chain: `id`, `company_id`, `chain_hash` (unique), `parent_hash` (nullable for genesis), `entry_type` (job_completed|inspection_passed|inspection_failed|checklist_completed|signature_captured|photo_attached|client_acknowledged|asset_serviced|override_applied), `subject_type`, `subject_id`, `actor_type` (user|system|ai), `actor_id`, `payload` (json — full context snapshot), `signed_at`, `created_at` (no updated_at — immutable)
  - `trust_evidence_attachments` — binary/file evidence: `ledger_entry_id`, `file_path`, `file_name`, `mime_type`, `file_size`, `checksum` (sha256), `attachment_type` (photo|signature|document|video), `captured_at`
  - `trust_chain_seals` — periodic chain integrity seals: `company_id`, `sealed_at`, `entry_count`, `root_hash`, `seal_hash`, `sealed_by`
- All tables: no `updated_at` on ledger entries (append-only), strict `created_at` only
- Use `Schema::hasTable()` guards

### Models
- `app/Models/Trust/TrustLedgerEntry.php`
  - `BelongsToCompany`, morphic `subject()` relationship
  - Override `save()` to prevent updates — throw `ImmutableRecordException` if `exists && isDirty()`
  - `scopeForSubject(Builder $q, string $type, int $id)` scope
  - `chainHash` computed from: `parent_hash + entry_type + subject_type + subject_id + actor_id + payload + signed_at`
- `app/Models/Trust/TrustEvidenceAttachment.php` — with `BelongsTo(TrustLedgerEntry)`
- `app/Models/Trust/TrustChainSeal.php` — periodic seal record

### Services
- `app/Services/Trust/TrustLedgerService.php`
  - `record(string $entryType, Model $subject, array $payload, ?User $actor = null): TrustLedgerEntry`
  - `buildChainHash(string $parentHash, string $entryType, string $subjectType, int $subjectId, ?int $actorId, array $payload, Carbon $signedAt): string`
  - `attachEvidence(TrustLedgerEntry $entry, UploadedFile $file): TrustEvidenceAttachment`
  - `getChain(Model $subject): Collection` — full ordered chain for a subject
  - `verifyChain(Model $subject): bool` — re-compute and verify all hashes
  - `sealChain(int $companyId): TrustChainSeal`
- `app/Services/Trust/TrustVerificationService.php`
  - `verifyEntry(TrustLedgerEntry $entry): bool`
  - `detectTampering(int $companyId): array` — returns any broken chain links
  - `generateComplianceProof(Model $subject): array` — exportable proof document

### Events
- `app/Events/Trust/LedgerEntryRecorded.php`
- `app/Events/Trust/ChainTamperingDetected.php`
- `app/Events/Trust/ChainSealed.php`

### Listeners
Attach to existing events to auto-record ledger entries:
- `app/Listeners/Trust/RecordJobCompletionOnLedger.php` — listens to `ServiceJobCompleted` (Work events)
- `app/Listeners/Trust/RecordInspectionResultOnLedger.php` — listens to `InspectionCompleted`, `InspectionFailed`
- `app/Listeners/Trust/RecordChecklistCompletionOnLedger.php` — listens to checklist run completed event
- `app/Listeners/Trust/RecordAssetServiceOnLedger.php` — listens to `AssetServiceEvent` creation

### Signals
- Emit via `SignalDispatcher` for: `trust.entry_recorded`, `trust.chain_verified`, `trust.tampering_detected`
- Signal payload must include: `chain_hash`, `subject_type`, `subject_id`, `entry_type`

### Controllers / Routes
- `app/Http/Controllers/Trust/TrustLedgerController.php`
  - `chain(string $subjectType, int $subjectId)` — full chain for a subject
  - `verify(string $subjectType, int $subjectId)` — chain integrity check
  - `proof(string $subjectType, int $subjectId)` — downloadable compliance proof
  - `seal(Request $request)` — trigger manual chain seal
- Register in `routes/core/` as new `trust.php` route file

### Tests
- `tests/Unit/Services/Trust/TrustLedgerServiceTest.php` — hash chain correctness
- `tests/Unit/Services/Trust/TrustVerificationServiceTest.php` — tamper detection
- `tests/Feature/Trust/TrustLedgerControllerTest.php`

### Docs Report
- `docs/modules/MODULE_03_TrustWorkLedger_report.md` — chain schema, entry type catalogue, compliance proof format, TitanRewind integration notes

### FSM Update
- Update `fsm_module_status.json` — set `trust_work_ledger` to `installed`

---

## Architecture Notes

- Ledger entries are STRICTLY immutable — no `update()` or `delete()` ever allowed on `trust_ledger_entries`
- Chain hash must be computed server-side using a deterministic algorithm (SHA-256 recommended)
- `parent_hash` for the first entry per subject is `null` (genesis entry)
- Subsequent entries MUST reference the previous entry's `chain_hash` as their `parent_hash`
- `verifyChain()` must recompute every hash from scratch and compare — do not trust stored hashes alone
- The `TrustLedgerService::record()` method must be wrapped in a database transaction with a lock to prevent race conditions on `parent_hash` lookup
- Must NOT replace `AuditTrail` — TrustWorkLedger is for client-facing proof of work; `AuditTrail` is for internal system audit
- TitanRewind operations must create ledger entries for any rollback that affects a completed job or inspection
- Evidence attachments must include SHA-256 checksum computed at upload time

---

## References

- `app/Titan/Signals/AuditTrail.php`
- `app/Titan/Signals/ProcessRecorder.php`
- `app/Titan/Signals/SignalDispatcher.php`
- `app/Extensions/TitanRewind/System/` (all 30 files)
- `app/Models/Work/ServiceJob.php`
- `app/Models/Inspection/InspectionInstance.php`
- `app/Models/Facility/AssetServiceEvent.php`
- `app/Models/Work/ChecklistRun.php`
- `app/Events/Work/` (all 16 events — attach listeners to relevant ones)
- `app/Events/Inspection/` (all 5 inspection events)
- `docs/nexuscore/` (trust, compliance, evidence design docs)
- `docs/titancore/` (TitanRewind and audit architecture)
