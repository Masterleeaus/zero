# MODULE 10 — TitanMesh: Federated Capability Exchange Engine

**Label:** `titan-module` `mesh` `federation` `multi-tenant` `capability-exchange`
**Milestone:** TitanZero Capability Expansion Pack v1
**Priority:** Medium

---

## Overview

Build the **TitanMesh** — a federated capability exchange layer that allows TitanZero instances (separate tenants or partner organisations) to discover, share, and execute capabilities across trust boundaries. A company that lacks a certified specialist can source one from a trusted partner through TitanMesh. Dispatch requests, capability queries, and execution results flow through a governed mesh protocol with full auditability.

TitanMesh is the most architecturally complex module. It builds on ALL prior modules: dispatch (01), capability registry (02), trust ledger (03), contracts (04), edge sync (05), and time graph (06). It must be implemented last and must not be activated until all prior modules are stable.

---

## Pre-Scan Requirement (MANDATORY — run before any implementation pass)

1. Read `app/Models/Work/DispatchAssignment.php` (Module 01 output) — mesh dispatch extends this
2. Read `app/Models/Team/TechnicianSkill.php` and `Certification.php` (Module 02 output) — federated capability queries
3. Read `app/Models/Trust/TrustLedgerEntry.php` (Module 03 output) — cross-tenant evidence
4. Read `app/Models/Work/ServiceAgreement.php` (Module 04 extended) — inter-company agreements
5. Read `app/Models/Sync/EdgeSyncQueue.php` (Module 05 output) — mesh sync integration
6. Read `app/Models/TimeGraph/ExecutionGraph.php` (Module 06 output) — cross-company timeline
7. Read `app/Titan/Signals/SignalDispatcher.php` and `AuditTrail.php` — signal governance
8. Read `app/Domains/Entity/Drivers/` — AI drivers for mesh intelligence
9. Read `docs/nexuscore/` — scan for federation, multi-tenant exchange, or mesh architecture docs
10. Read `docs/titancore/` — scan for any inter-company, partnership, or federated capability docs

---

## Canonical Models to Extend / Reference

- All Module 01–06 service classes — TitanMesh orchestrates them in a cross-company context
- `app/Titan/Signals/AuditTrail.php` — all mesh operations must be fully audited
- `app/Models/Concerns/BelongsToCompany.php` — mesh must NEVER violate company isolation

---

## Required Output Artifacts

### Migrations
- `database/migrations/YYYY_MM_DD_create_titan_mesh_tables.php`
  - `mesh_nodes` — registered peer companies/instances: `id`, `company_id` (the owning company), `node_id` (uuid — the peer's identifier), `node_name`, `node_url` (nullable — for API-connected peers), `trust_level` (observer|standard|trusted|partner), `public_key` (text — for signed request verification), `is_active`, `last_handshake_at`, `capabilities_hash` (string — hash of last known capability export), `created_at`, `updated_at`
  - `mesh_capability_exports` — capabilities this company advertises to peers: `company_id`, `capability_type` (skill|certification|job_type|service_area|equipment_type), `capability_value`, `available_count` (nullable — how many technicians hold it), `geographic_scope` (json — regions/territories covered), `is_exported` (bool — whether visible to mesh), `updated_at`
  - `mesh_dispatch_requests` — cross-company dispatch asks: `id`, `requesting_company_id`, `fulfilling_company_id` (nullable — assigned peer), `original_job_id` (nullable), `required_capabilities` (json), `location` (json — lat/lng or premises reference), `urgency` (low|normal|high|emergency), `status` (open|offered|accepted|executing|completed|rejected|expired), `offered_at`, `accepted_at`, `completed_at`, `mesh_job_reference` (string — peer's job id), `evidence_hash` (nullable — from TrustWorkLedger), `commission_rate` (decimal 5,4 nullable), `created_at`, `updated_at`
  - `mesh_trust_events` — trust history between nodes: `company_id`, `node_id` (peer), `event_type` (handshake|job_completed|dispute_raised|trust_upgraded|trust_downgraded|node_suspended), `payload` (json), `occurred_at`
  - `mesh_settlements` — financial settlement records for cross-company jobs: `mesh_dispatch_request_id`, `requesting_company_id`, `fulfilling_company_id`, `amount` (decimal 12,2), `commission_amount` (decimal 12,2), `net_amount` (decimal 12,2), `currency` (char 3 default 'AUD'), `status` (pending|invoiced|paid|disputed), `invoice_reference` (nullable), `settled_at`
- Use `Schema::hasTable()` / `Schema::hasColumn()` guards

### Models
- `app/Models/Mesh/MeshNode.php` — peer node with `BelongsToCompany`, trust level scopes
- `app/Models/Mesh/MeshCapabilityExport.php` — advertised capabilities
- `app/Models/Mesh/MeshDispatchRequest.php` — cross-company dispatch
- `app/Models/Mesh/MeshTrustEvent.php` — append-only trust history
- `app/Models/Mesh/MeshSettlement.php` — inter-company settlement

### Services
- `app/Services/Mesh/MeshRegistryService.php`
  - `registerNode(int $companyId, array $nodeData): MeshNode`
  - `performHandshake(MeshNode $node): bool`
  - `exportCapabilities(int $companyId): array`
  - `importCapabilities(MeshNode $node, array $capabilities): void`
  - `getTrustedPeers(int $companyId, string $minTrustLevel = 'standard'): Collection`
- `app/Services/Mesh/MeshDispatchService.php`
  - `requestFulfillment(ServiceJob $job, array $requiredCapabilities): MeshDispatchRequest`
  - `findFulfillingNodes(MeshDispatchRequest $request): Collection` — queries peer capability exports
  - `offerToNode(MeshDispatchRequest $request, MeshNode $node): void`
  - `acceptOffer(MeshDispatchRequest $request): void`
  - `completeRequest(MeshDispatchRequest $request, array $evidenceData): void`
  - `rejectOffer(MeshDispatchRequest $request, string $reason): void`
- `app/Services/Mesh/MeshTrustService.php`
  - `recordTrustEvent(MeshNode $node, string $eventType, array $payload): MeshTrustEvent`
  - `computeTrustScore(MeshNode $node): float`
  - `upgradeTrust(MeshNode $node, User $authorisedBy): void`
  - `suspendNode(MeshNode $node, string $reason, User $authorisedBy): void`
- `app/Services/Mesh/MeshSettlementService.php`
  - `calculateSettlement(MeshDispatchRequest $request): MeshSettlement`
  - `invoiceSettlement(MeshSettlement $settlement): void`
  - `getPendingSettlements(int $companyId): Collection`
- `app/Services/Mesh/MeshSignatureService.php`
  - `signPayload(array $payload, int $companyId): string` — HMAC/RSA signature
  - `verifyPayload(array $payload, string $signature, MeshNode $fromNode): bool`
  - `buildMeshEnvelope(array $payload, string $action): array`

### API Controllers (Mesh Protocol Endpoints)
- `app/Http/Controllers/Api/Mesh/MeshNodeController.php` — receives inbound mesh calls from peers
  - `POST /api/mesh/handshake` — peer registration/handshake
  - `POST /api/mesh/capabilities` — receive capability query from peer
  - `POST /api/mesh/dispatch/offer` — receive dispatch offer from peer
  - `POST /api/mesh/dispatch/accept` — peer accepts our offer
  - `POST /api/mesh/dispatch/complete` — peer reports completion with evidence
- `app/Http/Controllers/Mesh/MeshDashboardController.php` — internal admin UI
  - `nodes(Request $request)` — connected peer nodes
  - `requests(Request $request)` — active dispatch requests
  - `settlements(Request $request)` — pending/completed settlements
  - `trust(MeshNode $node)` — trust history for a node

### Events
- `app/Events/Mesh/MeshNodeHandshaked.php`
- `app/Events/Mesh/MeshDispatchRequested.php`
- `app/Events/Mesh/MeshDispatchAccepted.php`
- `app/Events/Mesh/MeshDispatchCompleted.php`
- `app/Events/Mesh/MeshTrustChanged.php`
- `app/Events/Mesh/MeshSettlementDue.php`

### Listeners
- `app/Listeners/Mesh/RecordMeshOperationOnTrustLedger.php` — every mesh transaction creates a ledger entry (Module 03)
- `app/Listeners/Mesh/RecordMeshEventOnTimeGraph.php` — mesh events enter execution graph (Module 06)
- `app/Listeners/Mesh/NotifyOnMeshDispatchAccepted.php`

### Signals
- Emit via `SignalDispatcher`: `mesh.node_registered`, `mesh.dispatch_requested`, `mesh.dispatch_completed`, `mesh.trust_changed`, `mesh.settlement_due`

### Tests
- `tests/Unit/Services/Mesh/MeshRegistryServiceTest.php`
- `tests/Unit/Services/Mesh/MeshSignatureServiceTest.php`
- `tests/Feature/Api/Mesh/MeshNodeControllerTest.php`

### Docs Report
- `docs/modules/MODULE_10_TitanMesh_report.md` — mesh protocol spec, trust model, capability exchange schema, settlement flow, security model (payload signing), cross-company data isolation guarantees

### FSM Update
- Update `fsm_module_status.json` — set `titan_mesh` to `installed`

---

## Architecture Notes

- **Security first**: All inbound mesh API calls MUST verify `MeshSignatureService::verifyPayload()` before any processing — reject unsigned or invalid-signature requests with 401
- **Data isolation is absolute**: A mesh node NEVER receives raw internal data — only exported capability summaries and completion evidence hashes. PII, financial data, and internal job details stay within the company boundary
- **Trust levels gate capabilities**: `observer` can query capabilities only; `standard` can receive dispatch offers; `trusted` can also push evidence; `partner` enables settlement
- **Commission model**: `mesh_dispatch_requests.commission_rate` is agreed per mesh agreement — `MeshSettlementService` applies it automatically on completion
- **Offline mesh**: Mesh protocol must queue operations if peer is unreachable — use `EdgeSyncQueue` pattern from Module 05
- **Payload signing**: Use HMAC-SHA256 with the peer's `public_key` as the shared secret for MVP; upgrade to asymmetric RSA for production
- **TrustLedger integration** (Module 03): Every mesh job completion must produce a ledger entry with `entry_type = 'mesh_job_completed'` — this is the cross-company proof of work
- **Activation gate**: TitanMesh should NOT be activated until Modules 01–06 are all in `installed` state in `fsm_module_status.json` — add a startup check in `MeshRegistryService::registerNode()`
- Must respect `company_id` scoping at every layer — the requesting and fulfilling companies are always distinct

---

## References

- `app/Models/Work/DispatchAssignment.php` (Module 01)
- `app/Models/Team/TechnicianSkill.php`, `Certification.php` (Module 02)
- `app/Models/Trust/TrustLedgerEntry.php` (Module 03)
- `app/Services/Trust/TrustLedgerService.php` (Module 03)
- `app/Models/Work/ServiceAgreement.php` (Module 04)
- `app/Models/Sync/EdgeSyncQueue.php` (Module 05)
- `app/Models/TimeGraph/ExecutionGraph.php` (Module 06)
- `app/Titan/Signals/SignalDispatcher.php`
- `app/Titan/Signals/AuditTrail.php`
- `app/Domains/Entity/Drivers/` (mesh intelligence)
- `docs/nexuscore/` (federation, multi-tenant, mesh docs)
- `docs/titancore/` (inter-company architecture docs)
