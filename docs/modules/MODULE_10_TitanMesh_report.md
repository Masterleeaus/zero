# MODULE 10 — TitanMesh: Federated Capability Exchange Engine

**Status:** `installed`
**Date:** 2026-04-04
**Activation gate:** Modules 01–06 must be in `installed` state in `fsm_module_status.json`

---

## Summary

TitanMesh is a federated capability exchange layer that allows separate TitanZero instances (tenants or partner organisations) to discover, share, and execute capabilities across trust boundaries. It introduces a governed mesh protocol with HMAC-SHA256 payload signing, trust-level gating, and full auditability.

---

## Artifacts Created

### Migration
| File | Tables |
|---|---|
| `database/migrations/2026_04_04_001000_create_titan_mesh_tables.php` | `mesh_nodes`, `mesh_capability_exports`, `mesh_dispatch_requests`, `mesh_trust_events`, `mesh_settlements` |

### Models (`app/Models/Mesh/`)
| Model | Description |
|---|---|
| `MeshNode` | Peer node registration. Trust level scopes: `observer`, `standard`, `trusted`, `partner`. |
| `MeshCapabilityExport` | Exported capability summaries (no PII). |
| `MeshDispatchRequest` | Cross-company dispatch fulfillment request lifecycle. |
| `MeshTrustEvent` | Append-only trust event log — immutable after creation. |
| `MeshSettlement` | Financial settlement record for fulfilled mesh jobs. |

### Services (`app/Services/Mesh/`)
| Service | Key Methods |
|---|---|
| `MeshRegistryService` | `registerNode()` (with activation gate), `performHandshake()`, `exportCapabilities()`, `getTrustedPeers()` |
| `MeshDispatchService` | `requestFulfillment()`, `findFulfillingNodes()`, `offerToNode()`, `acceptOffer()`, `completeRequest()` |
| `MeshTrustService` | `recordTrustEvent()`, `computeTrustScore()`, `upgradeTrust()`, `suspendNode()` |
| `MeshSettlementService` | `calculateSettlement()`, `invoiceSettlement()`, `getPendingSettlements()` |
| `MeshSignatureService` | `signPayload()`, `verifyPayload()`, `buildMeshEnvelope()` — HMAC-SHA256 |

### API Controllers (Mesh Protocol Endpoints)
| Controller | Endpoints |
|---|---|
| `app/Http/Controllers/Api/Mesh/MeshNodeController` | `POST /api/mesh/handshake`, `POST /api/mesh/capabilities`, `POST /api/mesh/dispatch/offer`, `POST /api/mesh/dispatch/accept`, `POST /api/mesh/dispatch/complete` |
| `app/Http/Controllers/Mesh/MeshDashboardController` | `GET /dashboard/mesh/nodes`, `GET /dashboard/mesh/requests`, `GET /dashboard/mesh/settlements`, `GET /dashboard/mesh/trust` |

### Events (`app/Events/Mesh/`)
- `MeshNodeHandshaked`
- `MeshDispatchRequested`
- `MeshDispatchAccepted`
- `MeshDispatchCompleted`
- `MeshTrustChanged`
- `MeshSettlementDue`

### Listeners (`app/Listeners/Mesh/`)
| Listener | Handles |
|---|---|
| `RecordMeshOperationOnTrustLedger` | `MeshNodeHandshaked`, `MeshDispatchCompleted` → TrustLedger (Module 03 integration, tolerant) |
| `RecordMeshEventOnTimeGraph` | `MeshNodeHandshaked`, `MeshDispatchRequested`, `MeshDispatchCompleted`, `MeshTrustChanged` → ExecutionTimeGraph (Module 06 integration, tolerant) |
| `NotifyOnMeshDispatchAccepted` | `MeshDispatchAccepted` → structured log / notification |

### Routes
- `routes/core/mesh.routes.php` — auto-loaded by `RouteServiceProvider`
  - Dashboard routes: `dashboard.mesh.*`
  - API routes: `api.mesh.*`

### Tests
- `tests/Unit/Services/Mesh/MeshRegistryServiceTest.php`
- `tests/Unit/Services/Mesh/MeshSignatureServiceTest.php`
- `tests/Feature/Api/Mesh/MeshNodeControllerTest.php`

---

## Architecture Decisions

### Security
- **All inbound mesh API calls** verify `MeshSignatureService::verifyPayload()` using the peer node's stored `public_key` (HMAC-SHA256). Unsigned or invalid requests return HTTP 401.
- **Data isolation**: peers receive only exported capability summaries and evidence hashes — no PII, financial data, or internal job details.

### Trust Levels
| Level | Permissions |
|---|---|
| `observer` | Query capabilities only |
| `standard` | Receive and send dispatch offers |
| `trusted` | Push completion evidence |
| `partner` | Settlement enabled |

### Activation Gate
`MeshRegistryService::registerNode()` reads `fsm_module_status.json` and throws a `RuntimeException` if any of the following keys are not in `installed` state:
- `titan_dispatch` (Module 01)
- `capability_registry` (Module 02)
- `trust_work_ledger` (Module 03)
- `titan_contracts` (Module 04)
- `edge_sync` (Module 05)
- `execution_time_graph` (Module 06)

### Module Integration (Tolerant)
Cross-module integrations (Trust Ledger, Execution Time Graph) are implemented with class-existence checks so TitanMesh remains functional even if the upstream modules are not yet deployed. This allows safe installation sequence flexibility.

### Offline Queue
Dispatch requests for unreachable peers can be queued using the `EdgeSyncQueue` pattern (Module 05) once that module is installed.

---

## Signals Emitted
- `mesh.node_registered`
- `mesh.dispatch_requested`
- `mesh.dispatch_completed`
- `mesh.trust_changed`
- `mesh.settlement_due`

---

## FSM Status
`fsm_module_status.json` → `titan_mesh: installed`
