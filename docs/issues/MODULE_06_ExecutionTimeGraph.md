# MODULE 06 — ExecutionTimeGraph: Temporal Lifecycle Replay Engine

**Label:** `titan-module` `time-graph` `lifecycle` `replay` `temporal`
**Milestone:** TitanZero Capability Expansion Pack v1
**Priority:** Medium-High

---

## Overview

Build the **ExecutionTimeGraph** — a temporal event sourcing layer that records the complete lifecycle of every service job, inspection, asset service event, and contract as a traversable time graph. This enables "replay" — re-running the exact sequence of events that led to any state — and "rewind" — identifying the precise moment something went wrong.

ExecutionTimeGraph is not a replacement for TitanRewind; it is the data substrate that makes TitanRewind intelligent. Where TitanRewind executes rollback, ExecutionTimeGraph answers "what happened, in what order, and why" — including all signal emissions, stage transitions, user decisions, AI actions, and external triggers.

---

## Pre-Scan Requirement (MANDATORY — run before any implementation pass)

1. Read `app/Extensions/TitanRewind/System/` — ALL 30 files, understand full rewind architecture
2. Read `app/Titan/Signals/` — ALL 21 files, understand `ProcessRecorder`, `ProcessStateMachine`, `AuditTrail`, `EnvelopeBuilder`
3. Read `app/Models/Work/ServiceJob.php` — all stage/status fields
4. Read `app/Models/Work/JobActivity.php` — existing activity log model (key reference point)
5. Read `app/Models/Inspection/InspectionInstance.php` — lifecycle fields
6. Read `app/Services/Work/JobStageService.php` and `JobActivityService.php` — transition patterns
7. Read `database/migrations/` — `job_activities`, `service_jobs`, `inspection_instances` table schemas
8. Read `docs/nexuscore/` — scan for temporal, event sourcing, lifecycle, or replay design docs
9. Read `docs/titancore/` — scan for TitanRewind integration and state machine docs
10. Read `app/Models/Trust/TrustLedgerEntry.php` (Module 03 output) — understand the ledger entry structure that feeds into the time graph

---

## Canonical Models to Extend / Reference

- `app/Models/Work/JobActivity.php` — existing activity pattern to extend
- `app/Extensions/TitanRewind/System/RewindEngine.php`
- `app/Extensions/TitanRewind/System/RewindDependencyGraphService.php`
- `app/Titan/Signals/ProcessRecorder.php`
- `app/Titan/Signals/ProcessStateMachine.php`
- `app/Titan/Signals/AuditTrail.php`

---

## Required Output Artifacts

### Migrations
- `database/migrations/YYYY_MM_DD_create_execution_time_graph_tables.php`
  - `execution_events` — the core time graph: `id`, `company_id`, `graph_id` (uuid — groups related events for one execution thread), `parent_event_id` (nullable self-ref — causal chain), `subject_type`, `subject_id`, `event_class` (fully qualified event class name), `event_type` (stage_transition|signal_emitted|user_action|ai_decision|system_trigger|external_event|rewind_applied|sync_received), `actor_type` (user|system|ai|external), `actor_id` (nullable), `payload` (json — full state snapshot at event time), `occurred_at` (datetime with microsecond precision), `sequence` (unsignedBigInteger — monotonic counter per graph_id), `created_at`
  - `execution_graphs` — named execution threads: `id`, `company_id`, `graph_id` (uuid unique), `root_subject_type`, `root_subject_id`, `title`, `status` (active|completed|rewound|archived), `started_at`, `completed_at`, `event_count`, `created_at`, `updated_at`
  - `execution_graph_checkpoints` — named snapshots for fast replay: `execution_graph_id`, `event_id` (FK execution_events), `label`, `state_snapshot` (json), `created_at`
- Use `Schema::hasTable()` / `Schema::hasColumn()` guards

### Models
- `app/Models/TimeGraph/ExecutionEvent.php`
  - `BelongsToCompany`, `BelongsTo(ExecutionGraph, 'graph_id', 'graph_id')` (by uuid)
  - `parent(): BelongsTo` (self-referential)
  - `children(): HasMany` (self-referential)
  - `scopeForSubject()`, `scopeForGraph()`, `scopeByEventType()`
- `app/Models/TimeGraph/ExecutionGraph.php`
  - `events(): HasMany(ExecutionEvent)`
  - `checkpoints(): HasMany(ExecutionGraphCheckpoint)`
  - `rootSubject(): MorphTo` (via root_subject_type/id)
- `app/Models/TimeGraph/ExecutionGraphCheckpoint.php`

### Services
- `app/Services/TimeGraph/ExecutionTimeGraphService.php`
  - `openGraph(Model $rootSubject, string $title): ExecutionGraph`
  - `record(string $graphId, string $eventClass, Model $subject, array $payload, string $eventType, string $actorType, ?int $actorId = null, ?int $parentEventId = null): ExecutionEvent`
  - `closeGraph(string $graphId): ExecutionGraph`
  - `getTimeline(string $graphId): Collection` — ordered by sequence
  - `replayFrom(ExecutionGraphCheckpoint $checkpoint): array` — replays events from checkpoint
  - `createCheckpoint(string $graphId, string $label): ExecutionGraphCheckpoint`
  - `findCausalChain(ExecutionEvent $event): Collection` — trace back through parent_event_id
- `app/Services/TimeGraph/ExecutionReplayService.php`
  - `buildReplayPlan(ExecutionGraph $graph, Carbon $toTime): array`
  - `describeDecision(ExecutionEvent $event): string` — human-readable event description
  - `exportTimeline(ExecutionGraph $graph): array` — structured export for UI rendering
  - `identifyAnomalies(ExecutionGraph $graph): array` — flag unusual durations or skipped steps

### Integration: Hook into Existing Systems
- Extend `app/Services/Work/JobStageService.php` — call `ExecutionTimeGraphService::record()` on every stage transition
- Extend `app/Titan/Signals/ProcessRecorder.php` — call `ExecutionTimeGraphService::record()` on every signal recorded
- Extend `app/Services/Work/JobActivityService.php` — map existing activity records into time graph events on write

### Events
- `app/Events/TimeGraph/ExecutionGraphOpened.php`
- `app/Events/TimeGraph/ExecutionGraphCompleted.php`
- `app/Events/TimeGraph/ExecutionCheckpointCreated.php`
- `app/Events/TimeGraph/ExecutionAnomalyDetected.php`

### Signals
- Emit via `SignalDispatcher`: `timegraph.event_recorded`, `timegraph.graph_completed`, `timegraph.anomaly_detected`
- Do NOT create infinite loops — time graph recording must NOT itself trigger time graph events

### Controllers / Routes
- `app/Http/Controllers/TimeGraph/ExecutionTimeGraphController.php`
  - `timeline(string $graphId)` — full ordered event timeline
  - `graph(string $subjectType, int $subjectId)` — get graph for a subject
  - `checkpoint(Request $request, string $graphId)` — create checkpoint
  - `replay(string $graphId, int $fromEventId)` — replay plan from event
  - `describe(int $eventId)` — human-readable event description
- Register in `routes/core/` as new `timegraph.php` route file

### Tests
- `tests/Unit/Services/TimeGraph/ExecutionTimeGraphServiceTest.php`
- `tests/Unit/Services/TimeGraph/ExecutionReplayServiceTest.php`
- `tests/Feature/TimeGraph/ExecutionTimeGraphControllerTest.php`

### Docs Report
- `docs/modules/MODULE_06_ExecutionTimeGraph_report.md` — graph schema, event type catalogue, causal chain model, TitanRewind integration points, anomaly detection rules

### FSM Update
- Update `fsm_module_status.json` — set `execution_time_graph` to `installed`

---

## Architecture Notes

- `graph_id` is a UUID assigned at job/inspection creation — one graph per top-level execution thread
- `sequence` must be a monotonic counter scoped to `graph_id` — use DB-level advisory locks or a `sequences` table for race-safe increment
- `parent_event_id` enables causal tracing: a signal fired by a stage transition has the transition event as its parent
- `occurred_at` must include microsecond precision for correct ordering of same-millisecond events
- Integration with `ProcessRecorder` must NOT add latency to the signal hot path — use queued listeners
- Time graph events are append-only — no updates to `execution_events` ever
- Checkpoint state snapshots must capture the full Eloquent model attributes at that point in time
- Anomaly detection: flag events where `occurred_at` gap > 2x the historical median for that event type pair
- Must integrate with TitanRewind: when a rewind occurs, record a `rewind_applied` event on the affected graph

---

## References

- `app/Extensions/TitanRewind/System/` (all 30 files)
- `app/Titan/Signals/ProcessRecorder.php`
- `app/Titan/Signals/ProcessStateMachine.php`
- `app/Titan/Signals/AuditTrail.php`
- `app/Titan/Signals/SignalDispatcher.php`
- `app/Models/Work/JobActivity.php`
- `app/Services/Work/JobStageService.php`
- `app/Services/Work/JobActivityService.php`
- `app/Models/Trust/TrustLedgerEntry.php` (Module 03 output)
- `docs/nexuscore/` (temporal, event sourcing, lifecycle docs)
- `docs/titancore/` (TitanRewind and state machine docs)
