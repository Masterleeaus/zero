# MODULE 06 — ExecutionTimeGraph

**Status:** Installed  
**Date:** 2026-04-03  
**Domain:** TimeGraph

---

## Purpose

ExecutionTimeGraph provides a persistent, ordered, causal audit trail for any lifecycle-driven entity (e.g. ServiceJob). It records every stage transition, signal emission, user action, AI decision, and system trigger as a sequenced event on a named graph. Graphs can be replayed, checkpointed, and analysed for timing anomalies.

---

## Tables

| Table | Purpose |
|---|---|
| `execution_graphs` | Root graph per entity; holds status, event count, started/completed timestamps |
| `execution_events` | Individual time-ordered events with microsecond `occurred_at`, sequence number, parent link, and payload |
| `execution_graph_checkpoints` | Snapshots of graph state at a named point in time |

---

## Models

| Class | Namespace |
|---|---|
| `ExecutionGraph` | `App\Models\TimeGraph` |
| `ExecutionEvent` | `App\Models\TimeGraph` |
| `ExecutionGraphCheckpoint` | `App\Models\TimeGraph` |

All models using `BelongsToCompany` call `withoutGlobalScope('company')` in service/controller queries to avoid test-context scope issues.

---

## Services

| Class | Responsibility |
|---|---|
| `ExecutionTimeGraphService` | Open/close graphs, record events, create checkpoints, find causal chains, flag anomalies |
| `ExecutionReplayService` | Build replay plans up to a given time, export full timeline, identify timing anomalies |

---

## Events

| Event | Trigger |
|---|---|
| `ExecutionGraphOpened` | Graph created |
| `ExecutionGraphCompleted` | Graph closed |
| `ExecutionCheckpointCreated` | Checkpoint saved |
| `ExecutionAnomalyDetected` | Anomaly detected via `flagAnomalies()` |

---

## Controller & Routes

**Controller:** `App\Http\Controllers\TimeGraph\ExecutionTimeGraphController`  
**Route file:** `routes/core/timegraph.routes.php` (auto-loaded by `RouteServiceProvider`)

| Method | Route | Name |
|---|---|---|
| GET | `dashboard/timegraph/{graphId}/timeline` | `dashboard.timegraph.timeline` |
| GET | `dashboard/timegraph/{graphId}` | `dashboard.timegraph.graph` |
| POST | `dashboard/timegraph/{graphId}/checkpoint` | `dashboard.timegraph.checkpoint` |
| POST | `dashboard/timegraph/{graphId}/replay` | `dashboard.timegraph.replay` |
| GET | `dashboard/timegraph/{graphId}/describe` | `dashboard.timegraph.describe` |

---

## Integrations

### JobStageService
`app/Services/Work/JobStageService.php` — after `JobReadyForInvoice::dispatch()`, records a `stage_transition` event on any active graph for the job. Wrapped in `try/catch` so graph failures never disrupt the transition.

### ProcessRecorder
`app/Titan/Signals/ProcessRecorder.php` — after `auditTrail->recordEntry()`, dispatches `RecordSignalToTimeGraph` as a queued job. Non-critical path.

### Job: RecordSignalToTimeGraph
`app/Jobs/TimeGraph/RecordSignalToTimeGraph.php` — resolves the entity from the signal payload, finds the active graph, and records a `signal_emitted` event.

---

## Tests

| File | Type |
|---|---|
| `tests/Unit/Services/TimeGraph/ExecutionTimeGraphServiceTest.php` | Unit |
| `tests/Unit/Services/TimeGraph/ExecutionReplayServiceTest.php` | Unit |
| `tests/Feature/TimeGraph/ExecutionTimeGraphControllerTest.php` | Feature |
