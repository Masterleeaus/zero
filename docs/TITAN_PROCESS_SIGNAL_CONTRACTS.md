# TITAN PROCESS & SIGNAL CONTRACTS

## Overview

Prompt 3 introduces two contract interfaces that normalise cross-domain access
to the process lifecycle and signal pipeline.

These contracts do NOT replace or override existing implementations.
They define the canonical interface that higher-level services must use.

---

## ProcessContract

**Location:** `app/Titan/Core/Contracts/ProcessContract.php`
**Namespace:** `App\Titan\Core\Contracts`

Defines the interface for process lifecycle integration. Aligns with:
- `docs/titancore/21_LIFECYCLE_ENGINE_STATE_MACHINE.md`
- `docs/titancore/41_PROCESS_ENGINE_OVERVIEW.md`

### Methods

| Method | Purpose |
|---|---|
| `begin(array $payload)` | Begin a new process (company_id, entity_type, domain required) |
| `transition(string $processId, string $toState, array $meta)` | State transition |
| `requiresApproval(string $processId)` | Is process in awaiting-approval? |
| `isRewindEligible(string $processId)` | Is process in processed/rewinding? |
| `auditTrail(string $processId)` | Full audit trail |
| `validTransitions(string $fromState)` | List valid next states |
| `currentState(string $processId)` | Current state string |
| `linkSignal(string $processId, string $signalId)` | Link to signal |
| `linkRewind(string $processId, string $rewindRef)` | Link to rewind snapshot |

### State Machine (canonical)

```
initiated → signal-queued → awaiting-validation
                          → validation-approved → awaiting-processing → processing → processed → rewinding
                          → validation-rejected → initiated
                          → awaiting-approval → processing (approved)
                                              → approval-rejected → initiated
```

---

## SignalContract

**Location:** `app/Titan/Core/Contracts/SignalContract.php`
**Namespace:** `App\Titan\Core\Contracts`

Normalises signal envelope structure, AI-resolution eligibility, approval gating,
rewind references, and dispatch lifecycle hooks. Aligns with:
- `docs/titancore/14_SIGNAL_ENVELOPE_SPEC.md`
- `docs/titancore/27_SIGNAL_TO_AI_PROCESS_FLOW.md`
- `docs/titancore/29_AI_APPROVAL_GOVERNANCE_MODEL.md`

### Methods

| Method | Purpose |
|---|---|
| `normalise(array $payload)` | Normalise to canonical signal structure |
| `isAiEligible(array $signal)` | Can AI resolve this signal? |
| `approvalGate(array $signal)` | Approval chain + requires_approval metadata |
| `withRewindRef(array $signal, string $rewindRef)` | Attach rewind reference |
| `dispatch(array $signal)` | Dispatch through canonical pipeline |
| `recordDispatch(array $signal, array $meta)` | Write audit trail entry |
| `envelope(array $signals, array $context)` | Build canonical envelope |

---

## config/titan_process.php

Key settings:

```php
'transitions' => [...],          // canonical valid state transitions
'approval_states' => [...],      // states requiring human approval
'rewind_eligible_states' => [...], // states eligible for rewind
'signal_on_transition' => true,  // emit signal on every transition
'audit_every_transition' => true, // write audit entry on every transition
'memory_snapshot_on_completion' => true, // TitanMemoryService::snapshot() on processed
```

---

## Integration Points

Both contracts integrate with:

| Component | Connection |
|---|---|
| `SignalDispatcher` | signal dispatch pipeline |
| `SignalsService` | signal ingest + publish |
| `EnvelopeBuilder` | canonical envelope construction |
| `ProcessRecorder` | process record creation |
| `ApprovalChain` | approval determination |
| `AuditTrail` | audit entry recording |
| `PulseSubscriber` | automation triggers |
| `RewindSubscriber` | rewind event hooks |
| `ZeroSubscriber` | Zero core hooks |

---

## Non-Goals

- These contracts do NOT replace `ProcessStateMachine`
- These contracts do NOT replace `SignalDispatcher`
- These contracts are interfaces only — no standalone implementations
