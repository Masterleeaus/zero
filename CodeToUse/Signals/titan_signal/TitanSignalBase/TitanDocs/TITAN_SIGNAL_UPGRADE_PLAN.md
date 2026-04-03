# Titan Signal Engine: Specification → Implementation Upgrade Plan

**Status**: Implementation Phase 1 (Foundations) → Phase 2 (Validation & Approval)  
**Target**: Full Signal & Processing Engine as per PDF specification  
**Timeline**: Multi-sprint incremental delivery  
**Architecture**: MagicAI Laravel extension, tenant-aware (`company_id`), flow-based signal processing

---

## Executive Summary

The current **next-pass implementation** (TitanSignalBase) establishes the **core recording, emission, and storage layers** from the PDF specification. This plan outlines a systematic upgrade path to implement the **complete specification** including validation chains, approval routing, state management, and audit trails.

### Current State vs. Target State

| Layer | PDF Spec | Current Implementation | Status |
|-------|----------|----------------------|--------|
| **Process Recording** | ✓ ProcessRecord, ProcessRecorder | ✓ ProcessRecorder (basic) | 70% Complete |
| **Signal Emission** | ✓ SignalEmitter, broadcast | ✓ SignalNormalizer, ingest | 75% Complete |
| **State Management** | ✓ ProcessStateMachine, transitions | Partial (current_state field) | 40% Complete |
| **Validation Layer** | ✓ SignalValidator, multi-check | Not started | 0% Complete |
| **Approval Routing** | ✓ ApprovalChain, AI-driven | Not started | 0% Complete |
| **Audit & History** | ✓ Complete trail, timeline | Partial (created_at tracking) | 20% Complete |
| **Offline & Sync** | ✓ Queue, broadcast strategy | Not started | 0% Complete |
| **Providers (Work/Money)** | Mentioned in spec | WorkSignalsProvider, MoneySignalsProvider | 60% Complete |

---

## Phase 1: Foundation (CURRENT – Weeks 1-4)

### ✓ Completed
- Basic ProcessRecorder (record method)
- Signal canonical object (Signal.php)
- SignalNormalizer
- Database schemas (tz_processes, tz_signals, tz_process_states)
- Basic SignalsService (ingest, store, feed, all)
- Two signal providers (Work, Money)
- API routing skeleton
- Configuration files

### Remaining Phase 1 Tasks

#### 1.1 Enhance ProcessRecorder → Full Specification
**File**: `app/Titan/Signals/ProcessRecorder.php`

**Changes**:
```php
class ProcessRecorder
{
    // NEW: Record from voice transcript (intent parsing)
    public function recordFromVoice(string $transcript, int $userId): array
    
    // NEW: Record from UI form submission
    public function recordFromUI(array $formData, int $userId): array
    
    // NEW: Capture context (permissions, device, location)
    private function buildContext(int $userId, ?string $originatingNode): array
    
    // NEW: Parse voice intent (delegate to CreatiCore or similar)
    private function parseVoiceIntent(string $transcript): array
    
    // NEW: Fetch user role & permissions
    private function getUserRole(int $userId): string
    private function getUserPermissions(int $userId): array
}
```

**Effort**: 2-3 days  
**Dependencies**: Voice intent parsing integration (CreatiCore/Claude)

---

#### 1.2 Build Complete ProcessStateMachine
**File**: `app/Titan/Signals/ProcessStateMachine.php`

**Current**: Mostly stub class  
**Target**: Full state machine with validation

```php
class ProcessStateMachine
{
    const VALID_TRANSITIONS = [
        'initiated' => ['signal-queued', 'cancelled'],
        'signal-queued' => ['awaiting-validation', 'cancelled'],
        'awaiting-validation' => ['validation-approved', 'validation-rejected', 'conflict-hold'],
        'validation-approved' => ['awaiting-processing', 'processing'],
        'awaiting-processing' => ['processing', 'processing-rejected', 'awaiting-approval'],
        'awaiting-approval' => ['processing', 'approval-rejected', 'awaiting-more-info'],
        'processing' => ['processed', 'processing-error', 'processing-hold'],
        'processed' => ['rewinding'],
        'validation-rejected' => ['initiated'],
        'approval-rejected' => ['initiated'],
    ];
    
    // NEW: Enforce transition rules
    public function transitionState(string $processId, string $newState, ?array $metadata): array
    
    // NEW: Emit state-change signal to automation engine
    private function emitStateChangeSignal(string $processId, string $newState): void
    
    // NEW: Notify user/system of transition
    private function notifyStateChange(string $processId, string $newState): void
}
```

**Effort**: 3-4 days  
**Dependencies**: Database audit logging, notification system

---

#### 1.3 Expand Signal.php with Validation/Processing Fields
**File**: `app/Titan/Signals/Signal.php`

**Add properties**:
```php
public ?string $signalStatus = 'new';  // new|validated|approved|processing|processed
public ?string $validationResult = null;  // APPROVED|REJECTED|HOLD
public array $validationErrors = [];
public array $validationWarnings = [];
public array $approvalChain = [];
public ?int $nextApproverId = null;
public array $approvedBy = [];
public ?int $processedEntityId = null;
public ?string $processedAt = null;
public ?string $rewindFrom = null;
public ?string $rolledBackBy = null;
```

**Effort**: 1 day  
**No dependencies**

---

#### 1.4 Add Audit Trail Table & Methods
**File**: `database/schemas/tz_audit_log.sql`

```sql
CREATE TABLE IF NOT EXISTS tz_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id VARCHAR(80) NOT NULL,
    signal_id VARCHAR(80) NULL,
    action VARCHAR(80) NOT NULL,  -- validated|approved|rejected|rewound
    performed_by BIGINT UNSIGNED NULL,
    details JSON NULL,
    created_at TIMESTAMP NULL,
    INDEX idx_tz_audit_log_process (process_id, created_at),
    INDEX idx_tz_audit_log_action (action, created_at)
);
```

**Create AuditTrail service**:
```php
class AuditTrail
{
    public function recordEntry(string $processId, string $action, array $details): void
    public function getHistory(string $processId): array
    public function printTimeline(string $processId): string
}
```

**Effort**: 2 days  
**No dependencies**

---

#### 1.5 Create Signal Queue Table (Offline Support)
**File**: `database/schemas/tz_signal_queue.sql`

```sql
CREATE TABLE IF NOT EXISTS tz_signal_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    signal_id VARCHAR(80) NOT NULL,
    payload JSON NOT NULL,
    broadcast_at TIMESTAMP NULL,
    broadcast_status VARCHAR(32) DEFAULT 'pending',  -- pending|sent|failed
    retry_count INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    INDEX idx_tz_signal_queue_status (broadcast_status, created_at)
);
```

**Effort**: 1 day  
**Dependencies**: Queue processor (phase 2)

---

### Phase 1 Deliverables
- ✓ Full ProcessRecorder with voice/UI paths
- ✓ Complete ProcessStateMachine with state validation
- ✓ Enhanced Signal object with full field set
- ✓ AuditTrail service & schema
- ✓ Signal queue table for offline support
- **Acceptance**: All processes flow through state machine; audit trail records every transition

---

## Phase 2: Validation & Approval (Weeks 5-10)

### 2.1 Build SignalValidator with Multi-Check Pipeline
**File**: `app/Titan/Signals/SignalValidator.php`

**New class implementing 5-check pipeline**:

```php
class SignalValidator
{
    private array $checks = [
        'structure',      // Required fields present
        'schema',         // Type validation
        'authorization',  // User permissions
        'conflicts',      // Duplicates, business rules
        'ai_logic',       // Claude/LogiCore validation
    ];
    
    public function validate(Signal $signal): ValidationResult
    {
        $results = [];
        
        foreach ($this->checks as $check) {
            $method = "check_{$check}";
            $results[$check] = $this->$method($signal);
        }
        
        return new ValidationResult($results);
    }
    
    private function checkStructure(Signal $signal): CheckResult
    private function checkSchema(Signal $signal): CheckResult
    private function checkAuthorization(Signal $signal): CheckResult
    private function detectConflicts(Signal $signal): CheckResult
    private function runAIValidation(Signal $signal): CheckResult
}
```

**Effort**: 5-6 days  
**Dependencies**: 
- CreatiCore (intent parsing, AI validation)
- User permissions model
- Conflict detection queries

---

### 2.2 Build ApprovalChain Router
**File**: `app/Titan/Signals/ApprovalChain.php`

**Features**:
- Configuration-based approvers by entity_type
- AI-driven approver suggestion (LogiCore)
- Multi-level approval (team lead → manager → director)
- Escalation rules (high value, high risk)

```php
class ApprovalChain
{
    public function determineApprovers(Signal $signal): array
    {
        // 1. Load config approvers for entity_type
        $approvers = $this->getConfigApprovers($signal->type);
        
        // 2. Ask AI (LogiCore) for additional approvers
        $aiSuggestion = $this->askAIForApprovers($signal);
        $approvers = array_merge($approvers, $aiSuggestion);
        
        // 3. Deduplicate and return
        return array_unique($approvers);
    }
    
    public function pushToApprovalQueue(Signal $signal): void
    {
        $approvers = $this->determineApprovers($signal);
        
        // Update process state
        $this->updateProcessState($signal->processId, 'awaiting-approval', [
            'approval_chain' => $approvers,
            'next_approver' => $approvers[0] ?? null,
        ]);
        
        // Notify each approver
        foreach ($approvers as $approverId) {
            $this->notifyApprover($approverId, $signal);
        }
    }
}
```

**Config file** (`config/titan_signal.php`):
```php
'approval_config' => [
    'booking' => [
        'requires_human_approval' => false,
        'default_approvers' => [],
    ],
    'invoice' => [
        'requires_human_approval' => true,
        'default_approvers' => ['role:manager'],
        'escalation' => [
            'amount > 5000' => ['role:director'],
        ],
    ],
],
```

**Effort**: 5-6 days  
**Dependencies**: 
- LogiCore for approver suggestion
- Notification system
- User role/permission model

---

### 2.3 Integrate Validation → State Flow
**Files**: 
- `app/Titan/Signals/SignalsService.php`
- `routes/titan_signals.php`

**Workflow**:
```
ingestSignal(payload)
  ↓
normalize()
  ↓
validateSignal() → ValidationResult
  ↓ (if APPROVED)
determineApprovers() → ApprovalChain
  ↓
transitionState(awaiting-approval)
  ↓
notifyApprovers()
  ↓
store(signal)
```

**New API endpoints**:
- `POST /api/signals/ingest` (existing, enhance with validation)
- `POST /api/signals/approve/:processId` (approve signal)
- `POST /api/signals/reject/:processId` (reject with reason)
- `GET /api/signals/approvals` (pending approvals for user)

**Effort**: 3-4 days  
**Dependencies**: Above validators & chain routers

---

### 2.4 Build Rewind/Rollback Engine
**File**: `app/Titan/Signals/RewindEngine.php`

**Concept**: User can "rewind" a processed signal, creating a corrected version

```php
class RewindEngine
{
    public function rewind(string $processId, array $correctedData): array
    {
        // 1. Get original process
        $original = DB::table('tz_processes')->find($processId);
        
        // 2. Create new corrected process
        $rewound = [
            'process_id' => 'proc-'.str_replace('.', '-', uniqid('', true)),
            'rewind_from' => $processId,
            'data' => $correctedData,
            ...
        ];
        
        // 3. Record transition in audit trail
        $this->auditTrail->recordEntry($processId, 'rewound', [
            'rewound_to' => $rewound['process_id'],
        ]);
        
        // 4. Flow corrected process through validation again
        return $this->signalsService->ingest($rewound);
    }
}
```

**Effort**: 3-4 days  
**Dependencies**: ProcessRecorder, SignalValidator

---

### Phase 2 Deliverables
- ✓ SignalValidator with 5-check pipeline
- ✓ ApprovalChain with AI-driven routing
- ✓ Integrated validation → approval flow
- ✓ Rewind/rollback capability
- ✓ New API endpoints for approval workflow
- **Acceptance**: Signals cannot process without validation; approvals chain users; rewind creates corrected processes

---

## Phase 3: Providers & Specialized Flows (Weeks 11-14)

### 3.1 Enhance WorkSignalsProvider
**File**: `app/Titan/Signals/Providers/WorkSignalsProvider.php`

**Signals to emit**:
- `job.created` → job booked
- `job.started` → field tech arrives
- `job.completed` → job done
- `job.cancelled` → job cancelled
- `assignment.updated` → reassigned
- `route.optimized` → route plan changed

**Effort**: 3-4 days

---

### 3.2 Enhance MoneySignalsProvider
**File**: `app/Titan/Signals/Providers/MoneySignalsProvider.php`

**Signals to emit**:
- `invoice.created` → new invoice
- `invoice.paid` → payment received
- `invoice.overdue` → payment past due
- `payment.processed` → transaction complete
- `refund.issued` → refund given

**Effort**: 3-4 days

---

### 3.3 Add GovernanceSignalsProvider
**File**: `app/Titan/Signals/Providers/GovernanceSignalsProvider.php`

**New provider for policy/compliance signals**:
- `policy.violated` → rule broken
- `compliance.check.failed` → audit failed
- `access.denied` → permission denied
- `security.event` → suspicious activity

**Effort**: 3-4 days

---

### 3.4 Add RewindProvider
**File**: `app/Titan/Signals/Providers/RewindProvider.php`

**Signals for rewind/correction workflows**:
- `process.rewound` → correction submitted
- `process.rolled_back` → original replaced

**Effort**: 2-3 days

---

### Phase 3 Deliverables
- ✓ Fully featured WorkSignalsProvider
- ✓ Fully featured MoneySignalsProvider
- ✓ New GovernanceSignalsProvider
- ✓ New RewindProvider
- **Acceptance**: All business domains emit rich signals; SignalsService aggregates all providers

---

## Phase 4: Offline & Sync (Weeks 15-16)

### 4.1 Implement Signal Queue Processing
**File**: `app/Titan/Signals/QueueProcessor.php`

```php
class QueueProcessor
{
    // Runs on schedule: every 30 seconds when online
    public function processPendingQueue(): void
    {
        $pending = DB::table('tz_signal_queue')
            ->where('broadcast_status', 'pending')
            ->limit(50)
            ->get();
        
        foreach ($pending as $queuedSignal) {
            try {
                $this->broadcastSignal($queuedSignal);
                $this->markSent($queuedSignal->id);
            } catch (Exception $e) {
                $this->incrementRetry($queuedSignal->id);
            }
        }
    }
    
    private function broadcastSignal(object $queuedSignal): void
    {
        // POST to signal bus, server, or cloud
        // Implements exponential backoff, max retries
    }
}
```

**Effort**: 2-3 days

---

### 4.2 Add Sync Status Tracking
**File**: `database/schemas/tz_sync_status.sql`

```sql
CREATE TABLE IF NOT EXISTS tz_sync_status (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    device_id VARCHAR(80) NOT NULL,
    last_sync TIMESTAMP NULL,
    pending_count INT DEFAULT 0,
    offline_mode TINYINT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_tz_sync_status_device (company_id, device_id)
);
```

**Effort**: 1 day

---

### Phase 4 Deliverables
- ✓ Queue processor with retry logic
- ✓ Sync status tracking table
- ✓ Offline-first signal emission support
- **Acceptance**: Signals queue when offline; sync automatically when back online

---

## Phase 5: Advanced Features (Weeks 17-20)

### 5.1 AI Validation Enhancement
Integrate with **LogiCore** (o3) for advanced validation:
- Pattern recognition (anomalies)
- Contextual rule checking
- Risk scoring

**Effort**: 3-4 days

### 5.2 Automation Engine Hooks
Implement signal → action triggers:
- "When invoice.paid, send receipt email"
- "When job.completed, schedule follow-up"

**Effort**: 3-4 days

### 5.3 Signal Aggregation & Envelopes
Full implementation of EnvelopeBuilder for Zero integration:

```php
class EnvelopeBuilder
{
    public function build(int $companyId, ?int $teamId = null): array
    {
        return [
            'company_id' => $companyId,
            'team_id' => $teamId,
            'actor_id' => Auth::id(),
            'signals' => $this->getSignals($companyId, $teamId),
            'summary' => $this->generateSummary(),
            'meta' => [
                'generated_at' => now(),
                'signal_count' => count($signals),
            ],
        ];
    }
}
```

**Effort**: 2-3 days

### 5.4 Dashboard & Monitoring
Admin dashboard for signal health:
- Queue status
- Validation hit rate
- Approval chain performance
- Rewind frequency

**Effort**: 4-5 days

---

## Implementation Roadmap (Gantt View)

```
Phase 1: Foundation
├── Week 1-2: ProcessRecorder, StateMachine
├── Week 2-3: Audit Trail, Queue Table
└── Week 3-4: Testing & refinement

Phase 2: Validation & Approval
├── Week 5-7: SignalValidator (5-check)
├── Week 7-9: ApprovalChain, integration
├── Week 9-10: Rewind Engine
└── Week 10: Testing & API docs

Phase 3: Providers
├── Week 11-12: Work & Money enhanced
├── Week 12-13: Governance, Rewind providers
└── Week 13-14: Testing

Phase 4: Offline & Sync
├── Week 15: Queue processor
├── Week 15-16: Sync status, offline mode
└── Week 16: Testing

Phase 5: Advanced
├── Week 17-18: AI validation, automation hooks
├── Week 18-19: Envelopes, Zero integration
├── Week 19-20: Dashboard & monitoring
└── Week 20: Final testing & documentation
```

---

## Database Migration Strategy

All migrations are **idempotent** and designed for incremental rollout.

### Phase 1 Migrations
```
migrations/
├── 2025_03_31_000001_create_tz_processes.php
├── 2025_03_31_000002_create_tz_signals.php
├── 2025_03_31_000003_create_tz_process_states.php
├── 2025_04_07_000004_create_tz_signal_queue.php
├── 2025_04_07_000005_create_tz_audit_log.php
└── 2025_04_07_000006_add_validation_fields_to_tz_signals.php
```

### Phase 2 Migrations
```
├── 2025_04_21_000007_create_tz_approvals.php
├── 2025_04_21_000008_create_tz_validation_results.php
└── 2025_04_28_000009_add_rewind_columns_to_tz_processes.php
```

### Phase 4 Migrations
```
├── 2025_05_26_000010_create_tz_sync_status.php
└── 2025_05_26_000011_add_sync_tracking_to_tz_signal_queue.php
```

---

## Integration Points

### With MagicAI v10
- Signal emission as **extension** in `app/Extensions/TitanSignal/`
- Register with ExtensionManager
- Access to MagicAI's AI cores (LogiCore, CreatiCore, etc.)
- Use MagicAI's Laravel bindings

### With TitanZero Platform
- **Processes** = business actions (bookings, invoices, jobs)
- **Signals** = events emitted by processes
- **Validation/Approval** = governance layer
- **Audit Trail** = compliance & history

### With Titan Omni
- Route signals to appropriate handlers
- Trigger chatbot/voice responses
- Queue voice notifications ("Your invoice was approved")

### With TitanBOS (Mobile)
- Consume signals via `/api/signals/feed`
- Offline queue signals locally
- Sync when back online

---

## Testing Strategy

### Unit Tests
- ProcessRecorder: voice → process, UI → process
- ProcessStateMachine: valid/invalid transitions
- SignalValidator: each check method
- ApprovalChain: config & AI routing

### Integration Tests
- Full flow: Process → Signal → Validation → Approval
- Offline queue → online sync
- Rewind → corrected process

### End-to-End Tests
- Field tech books job (voice) → signal → approved → job created
- Manager creates invoice → signal → needs approval → approved → invoice finalized
- Customer payment received → signal → auto-receipt email

---

## Configuration & Customization

**File**: `config/titan_signal.php`

```php
return [
    'enabled' => env('TITAN_SIGNAL_ENABLED', true),
    
    'database' => [
        'tables_prefix' => 'tz_',
        'use_sqlite' => env('TITAN_SIGNAL_USE_SQLITE', false),
    ],
    
    'validation' => [
        'checks' => ['structure', 'schema', 'authorization', 'conflicts', 'ai_logic'],
        'ai_timeout' => 5000, // ms
    ],
    
    'approval' => [
        'config' => [...entity-type approvals...],
        'use_ai_routing' => true,
    ],
    
    'offline' => [
        'queue_enabled' => true,
        'max_queue_size' => 10000,
        'sync_interval' => 30, // seconds
    ],
    
    'providers' => [
        'work' => true,
        'money' => true,
        'governance' => false,  // Phase 3
        'rewind' => true,
    ],
];
```

---

## Documentation Deliverables

### Architecture
- [ ] Signal & Processing Engine technical guide
- [ ] State machine diagram (Mermaid)
- [ ] API documentation with examples
- [ ] Database schema documentation

### Developer
- [ ] Quick start guide (record process, emit signal)
- [ ] Extending with custom providers
- [ ] Custom validation checks
- [ ] Adding approval configurations

### Operations
- [ ] Monitoring & alerting setup
- [ ] Queue processor scheduling
- [ ] Backup & recovery procedures
- [ ] Offline sync troubleshooting

---

## Risk Mitigation

| Risk | Mitigation |
|------|-----------|
| AI validation timeout | Fallback to basic checks; queue for retry |
| Offline queue overflow | Size limits; oldest entries dropped with warning |
| Approval loop deadlock | Timeout → auto-escalate; audit trail |
| State transition violation | Reject with clear error; suggest valid transitions |
| Duplicate signals | Conflict detection in validation layer |
| Approval chain loops | Deduplicate; limit chain depth to 5 levels |

---

## Success Criteria

✓ **Phase 1 Complete**: Every action creates audit trail; state transitions validated  
✓ **Phase 2 Complete**: Signals cannot process without validation; approval chains enforce governance  
✓ **Phase 3 Complete**: All business domains emit rich, normalized signals  
✓ **Phase 4 Complete**: Field agents work offline; sync automatically when back online  
✓ **Phase 5 Complete**: Signals trigger automations; dashboard shows real-time health  

**Final Goal**: TitanZero processes flow through governed signal pipeline; nothing executes directly; complete audit trail preserved forever.

---

## Next Steps

1. **Week 1**: Standup with team on Phase 1 scope
2. **Day 1-3**: Implement ProcessRecorder enhancements
3. **Day 4-6**: Build ProcessStateMachine
4. **Daily**: Commit to GitHub; update docs
5. **Week 2 Standup**: Review Phase 1 progress; adjust Phase 2 start date

---

**Document Version**: 1.0  
**Last Updated**: March 31, 2025  
**Author**: Claude (AI Architecture Assistant)  
**Status**: Ready for Implementation


## Implemented in this pass

- Added `SignalValidator` with structure, severity, duplicate, and lightweight business checks.
- Added `ApprovalChain` with severity and amount-based routing hints.
- Added `AuditTrail` service with timeline reconstruction.
- Upgraded `ProcessRecorder` to write initial process-state entries and audit entries.
- Upgraded `ProcessStateMachine` to persist transitions in `tz_process_states`.
- Expanded `Signal` and `tz_signals` schema with process and validation metadata.
- Added `tz_signal_queue` and `tz_audit_log` SQL schemas.
- Fixed service provider route loading and aligned route/config naming.
- Updated Zero bridge contract to company-first envelope access.
