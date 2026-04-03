# TITANZEROVNEXUS: ProcessRecord Lifecycle & State Machine

**Version:** 5.0  
**Document:** 003  
**Status:** Production Specification

---

## EXECUTIVE SUMMARY

**ProcessRecord** is the universal action lifecycle engine. Every user action (create job, send invoice, publish post) flows through the same state machine. This ensures:

- Consistent approval workflows
- Unified audit trails
- Guaranteed state transitions
- Automatic compensation (undo/rollback)
- Permission enforcement

One ProcessRecord table serves Work, Channel, Money, Growth, and Admin modes.

---

## I. PROCESSRECORD TABLE DESIGN

```sql
CREATE TABLE process_records (
    -- Identity
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    
    -- Tenant & context
    tenant_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    mode VARCHAR(50) NOT NULL, -- 'work', 'channel', 'money', 'growth', 'admin'
    
    -- Action metadata
    action_type VARCHAR(100) NOT NULL, -- 'create_job', 'send_invoice', 'publish_campaign'
    entity_type VARCHAR(50), -- What entity this action operates on ('job', 'invoice', 'campaign')
    entity_id BIGINT, -- Link to entities table
    
    -- State machine
    state VARCHAR(50) NOT NULL, -- proposed, processing, approved, executed, rejected, escalating, escalated, compensating, compensated
    
    -- Request details
    intent LONGTEXT, -- User's original intent (what they asked for)
    request_data JSON, -- Full request payload
    
    -- Processing
    processing_notes JSON, -- Validation steps taken, decisions made
    attempts INT DEFAULT 0, -- Retry count
    max_attempts INT DEFAULT 3,
    
    -- Approvals
    requires_approval BOOLEAN DEFAULT FALSE,
    approved_by BIGINT,
    approved_at TIMESTAMP,
    approval_reason VARCHAR(255),
    
    -- Execution
    execution_result JSON, -- What actually happened
    executed_by BIGINT,
    executed_at TIMESTAMP,
    execution_error VARCHAR(255),
    
    -- Escalation
    escalation_reason VARCHAR(255),
    escalated_to BIGINT,
    escalation_level VARCHAR(50), -- 'warning', 'error', 'critical'
    
    -- Compensation (undo logic)
    compensation_needed BOOLEAN DEFAULT FALSE,
    compensation_action VARCHAR(255), -- 'undo', 'notify', 'manual_review'
    compensation_executed_at TIMESTAMP,
    
    -- Audit
    rewind_checkpoint_id BIGINT, -- Link to Rewind for rollback
    audit_trail JSON, -- Immutable history
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP,
    
    -- Indexes
    INDEX idx_tenant_user (tenant_id, user_id),
    INDEX idx_tenant_state (tenant_id, state),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_mode (mode, state),
    INDEX idx_created (created_at),
    
    -- Enforcement
    FOREIGN KEY fk_tenant (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY fk_user (user_id) REFERENCES users(id),
    FOREIGN KEY fk_entity (entity_id) REFERENCES entities(id),
    
    CONSTRAINT ck_valid_state CHECK (state IN (
        'proposed',
        'processing',
        'approved',
        'executed',
        'rejected',
        'escalating',
        'escalated',
        'compensating',
        'compensated'
    )),
    
    CONSTRAINT ck_invalid_state_transitions CHECK (
        -- Examples of invalid transitions (enforced in application)
        -- executed → proposed (can't go backwards in normal flow)
        -- rejected → executed (already rejected)
        1=1 -- Actual validation in code
    )
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## II. STATE MACHINE DIAGRAM

```
                    ┌─ proposed
                    │
                    ├─→ processing
                    │       │
                    │       ├─→ approved
                    │       │       │
                    │       │       └─→ executed
                    │       │           │
                    │       │           └─→ completed [TERMINAL]
                    │       │
                    │       ├─→ rejected [TERMINAL]
                    │       │
                    │       └─→ escalating
                    │               │
                    │               └─→ escalated
                    │                   │
                    │                   ├─→ executed [via escalation]
                    │                   │
                    │                   └─→ rejected [escalation denied]
                    │
                    └─→ compensating
                        │
                        └─→ compensated [TERMINAL]
```

**Terminal states:** completed, rejected, compensated (no further transitions)

---

## III. STATE TRANSITIONS & VALIDATION

### **proposed → processing**

Transition when action is initiated and enters queue.

```php
class ProcessRecord {
    public function startProcessing(User $user): bool {
        if ($this->state !== 'proposed') {
            throw new InvalidStateTransitionException();
        }
        
        // Validate prerequisites before accepting
        if (!$this->validatePrerequisites()) {
            $this->setState('rejected');
            $this->addAuditEntry('Validation failed', 'prerequisite_check');
            return false;
        }
        
        $this->state = 'processing';
        $this->processing_notes = [
            'started_at' => now(),
            'validation_steps' => [
                'prerequisite_check' => 'passed',
                'permission_check' => 'pending'
            ]
        ];
        
        $this->save();
        Signal::emit('process.processing', $this);
        
        return true;
    }
}
```

---

### **processing → approved**

Transition when approval is granted (if needed) or bypassed (if not needed).

```php
public function approve(User $approver, string $reason = ''): bool {
    if ($this->state !== 'processing') {
        throw new InvalidStateTransitionException();
    }
    
    // Check if approver has authority
    if (!Gate::allows('approve_action', [$this->action_type, $approver])) {
        throw new PermissionDeniedException('User cannot approve this action');
    }
    
    $this->state = 'approved';
    $this->approved_by = $approver->id;
    $this->approved_at = now();
    $this->approval_reason = $reason;
    
    // Mark this moment for Rewind (can revert to before approval)
    $this->rewind_checkpoint_id = RewindCheckpoint::create([
        'process_record_id' => $this->id,
        'state_before' => 'processing',
        'state_after' => 'approved'
    ])->id;
    
    $this->save();
    Signal::emit('process.approved', $this);
    
    return true;
}

// Or directly approve without review if not needed
public function bypassApproval(): bool {
    if (!$this->requires_approval) {
        $this->state = 'approved';
        $this->save();
        Signal::emit('process.approved', $this);
        return true;
    }
    
    throw new ApprovalRequiredException();
}
```

---

### **approved → processing (back to processing if changes needed)**

Allow one cycle: if approved but execution needs changes, go back to processing.

```php
public function rejectAndRework(string $reason): bool {
    if ($this->state !== 'approved') {
        throw new InvalidStateTransitionException();
    }
    
    $this->state = 'processing';
    $this->processing_notes['rejected_for_rework'] = $reason;
    $this->attempts++;
    
    $this->save();
    Signal::emit('process.rework_requested', $this);
    
    return true;
}
```

---

### **approved → executed**

Transition when action is actually performed.

```php
public function execute(User $executor): bool {
    if ($this->state !== 'approved') {
        throw new InvalidStateTransitionException();
    }
    
    try {
        // Get the appropriate Sentinel for this mode
        $sentinel = Sentinel::for($this->mode);
        
        // Execute the action through Sentinel (which validates business rules)
        $result = $sentinel->execute($this);
        
        $this->state = 'executed';
        $this->executed_by = $executor->id;
        $this->executed_at = now();
        $this->execution_result = $result;
        
        // Link to entity that was created/modified
        if (isset($result['entity_id'])) {
            $this->entity_id = $result['entity_id'];
        }
        
        // Create Rewind checkpoint for this execution
        $this->rewind_checkpoint_id = RewindCheckpoint::create([
            'process_record_id' => $this->id,
            'state_before' => 'approved',
            'state_after' => 'executed',
            'execution_data' => $result
        ])->id;
        
        $this->save();
        Signal::emit('process.executed', $this);
        
        return true;
        
    } catch (ExecutionException $e) {
        $this->execution_error = $e->getMessage();
        $this->handleExecutionFailure($e);
        return false;
    }
}
```

---

### **executed → rejected (if error)**

Handle execution errors.

```php
private function handleExecutionFailure(ExecutionException $e): void {
    $this->state = 'rejected';
    $this->execution_error = $e->getMessage();
    $this->compensation_needed = true;
    
    // Decide what to do
    if ($e->isCritical()) {
        $this->state = 'escalating';
        $this->escalation_reason = $e->getMessage();
        $this->escalation_level = 'critical';
        Signal::emit('process.escalation_needed', $this);
    } else {
        // Auto-retry if not max attempts
        if ($this->attempts < $this->max_attempts) {
            $this->state = 'processing';
            $this->attempts++;
            Signal::emit('process.retry', $this);
        } else {
            $this->state = 'rejected';
            $this->compensation_action = 'manual_review';
            Signal::emit('process.failed_exhausted', $this);
        }
    }
    
    $this->save();
}
```

---

### **executed → completed**

Archive when action is fully done (after async tasks if any).

```php
public function markCompleted(): void {
    if ($this->state !== 'executed') {
        throw new InvalidStateTransitionException();
    }
    
    $this->state = 'completed';
    $this->completed_at = now();
    
    $this->save();
    Signal::emit('process.completed', $this);
}
```

---

### **Any state → escalating → escalated**

When human decision needed.

```php
public function escalate(string $reason, User $escalatedTo = null): void {
    if ($this->state === 'completed' || $this->state === 'rejected') {
        throw new CannotEscalateTerminalStateException();
    }
    
    $this->state = 'escalating';
    $this->escalation_reason = $reason;
    $this->escalation_level = 'medium';
    $this->escalated_to = $escalatedTo?->id;
    
    // Create Rewind checkpoint (can revert escalation)
    $this->rewind_checkpoint_id = RewindCheckpoint::create([
        'process_record_id' => $this->id,
        'state_before' => $this->state,
        'state_after' => 'escalating'
    ])->id;
    
    $this->save();
    Signal::emit('process.escalating', $this);
    
    // Notify admin
    Notification::send($escalatedTo ?? User::admins(), new ProcessEscalationNotification($this));
}
```

---

### **escalated → executed or rejected**

Escalation resolution.

```php
public function resolveEscalation(User $resolver, bool $approved): void {
    if ($this->state !== 'escalated') {
        throw new InvalidStateTransitionException();
    }
    
    if ($approved) {
        // Resume execution
        $this->state = 'approved';
        Signal::emit('process.escalation_approved', $this);
    } else {
        // Deny and reject
        $this->state = 'rejected';
        Signal::emit('process.escalation_denied', $this);
    }
    
    $this->save();
}
```

---

### **Any state → compensating → compensated**

Undo/rollback for errors.

```php
public function compensate(string $action = 'undo'): void {
    $this->state = 'compensating';
    $this->compensation_action = $action;
    
    try {
        if ($action === 'undo') {
            // Rewind to before this ProcessRecord was executed
            RewindEngine::rewindTo($this->rewind_checkpoint_id);
        } elseif ($action === 'notify') {
            // Just notify, don't undo
            Notification::send(User::relevant(), new ProcessCompensationNotification($this));
        } elseif ($action === 'manual_review') {
            // Flag for manual review
            AdminPanel::createTask('Manual review needed for ' . $this->action_type);
        }
        
        $this->state = 'compensated';
        $this->compensation_executed_at = now();
        Signal::emit('process.compensated', $this);
        
    } catch (CompensationException $e) {
        // Even compensation failed; escalate
        $this->escalate('Compensation failed: ' . $e->getMessage());
    }
    
    $this->save();
}
```

---

## IV. AUDIT TRAIL

Every transition is logged immutably.

```php
private function addAuditEntry(string $action, string $step = null): void {
    $this->audit_trail[] = [
        'timestamp' => now(),
        'action' => $action,
        'step' => $step,
        'state' => $this->state,
        'user_id' => Auth::id(),
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent()
    ];
    
    $this->save();
}
```

---

## V. PERMISSION ENFORCEMENT VIA PROCESSRECORD

Every action must have permission from AEGIS:

```php
public function validate(): bool {
    // Check permission to perform this action
    if (!Gate::allows($this->action_type, $this->user)) {
        $this->state = 'rejected';
        $this->addAuditEntry('Permission denied', 'permission_check');
        Signal::emit('process.permission_denied', $this);
        return false;
    }
    
    // Check if action requires approval
    if (Gate::requires_approval($this->action_type)) {
        $this->requires_approval = true;
    }
    
    // Check if action has business rule constraints
    if (!$this->validateBusinessRules()) {
        $this->state = 'rejected';
        return false;
    }
    
    return true;
}
```

---

## VI. RECOVERY & ROLLBACK

ProcessRecord integrates with Rewind:

```php
public function rollback(): void {
    if (!$this->rewind_checkpoint_id) {
        throw new NoCheckpointException();
    }
    
    // Rewind to checkpoint before this action
    RewindEngine::rewindTo($this->rewind_checkpoint_id);
    
    // Mark as compensated
    $this->state = 'compensated';
    $this->compensation_action = 'rollback';
    $this->compensation_executed_at = now();
    
    $this->save();
    Signal::emit('process.rolled_back', $this);
}
```

---

## VII. EXAMPLES

### **Example 1: Create a job (simple, no approval)**

```
User: "Schedule cleaning for tomorrow"
    ↓
ProcessRecord created (state: proposed)
    ↓
WorkSentinel.createJob() validates
    ↓
requires_approval = false (simple action)
    ↓
ProcessRecord state: processing
    ↓
AEGIS permission_gate: PASS
    ↓
ProcessRecord state: approved (bypassed review)
    ↓
WorkSentinel executes: creates Job entity
    ↓
ProcessRecord state: executed
    ↓
Signal: job.created emitted
    ↓
Subscribers triggered (ScheduleSubscriber, NotificationSubscriber)
    ↓
ProcessRecord state: completed
```

Timeline: 50ms (all synchronous)

---

### **Example 2: Issue large invoice (requires approval)**

```
User: "Create $50K invoice for Acme Corp"
    ↓
ProcessRecord created (state: proposed)
    ↓
MoneySentinel.createInvoice() validates
    ↓
requires_approval = true (large amount)
    ↓
ProcessRecord state: processing
    ↓
AEGIS permission_gate: PASS
    ↓
AEGIS approval_gate: Requires manager approval
    ↓
ProcessRecord state: processing (waiting for approval)
    ↓
[Manager notified, reviews for 10 minutes]
    ↓
Manager approves
    ↓
ProcessRecord state: approved
    ↓
MoneySentinel executes: creates Invoice entity
    ↓
ProcessRecord state: executed
    ↓
Signal: invoice.created emitted
    ↓
Subscribers: send email, schedule reminder, update forecast
    ↓
ProcessRecord state: completed
```

Timeline: 10 minutes (human approval required)

---

### **Example 3: Publish campaign (with error recovery)**

```
User: "Publish campaign to Instagram and Twitter"
    ↓
ProcessRecord created (state: proposed)
    ↓
GrowthSentinel.publishCampaign() validates
    ↓
requires_approval = false
    ↓
ProcessRecord state: processing → approved → executing
    ↓
Channel adapter attempts Instagram publish: SUCCESS
    ↓
Channel adapter attempts Twitter publish: FAILURE (API error)
    ↓
ProcessRecord.handleExecutionFailure()
    ↓
attempts = 1 / max_attempts = 3
    ↓
ProcessRecord state: processing (auto-retry)
    ↓
[Wait 5 seconds]
    ↓
Channel adapter retries Twitter: SUCCESS
    ↓
ProcessRecord state: executed
    ↓
Signal: campaign.published emitted
    ↓
Subscribers: schedule analytics collection, notify customer
    ↓
ProcessRecord state: completed
```

Timeline: 10 seconds (with automatic retry)

---

### **Example 4: Critical failure → escalation**

```
User: "Move $100K from account A to account B"
    ↓
ProcessRecord created
    ↓
AdminSentinel validates permission: PASS
    ↓
ProcessRecord state: processing → approved → executing
    ↓
Execution attempts fund transfer: FAILS (insufficient balance)
    ↓
ProcessRecord.handleExecutionFailure(): This is critical!
    ↓
ProcessRecord state: escalating
    ↓
Signal: process.escalation_needed emitted
    ↓
Admin notified of critical issue
    ↓
[Admin reviews, decides manual intervention needed]
    ↓
Admin resolves escalation: DENY
    ↓
ProcessRecord state: rejected
    ↓
ProcessRecord.compensate(): No action (transaction never executed)
    ↓
ProcessRecord state: compensated
```

Timeline: 2 minutes (requires human decision)

---

## VIII. INTEGRATION WITH SENTINELS

ProcessRecord is how Sentinels track actions:

```php
// In WorkSentinel
public function createJob(Company $company, array $jobData): ProcessRecord {
    // Create ProcessRecord
    $record = ProcessRecord::create([
        'tenant_id' => $company->tenant_id,
        'user_id' => Auth::id(),
        'mode' => 'work',
        'action_type' => 'create_job',
        'state' => 'proposed',
        'intent' => 'User wants to create a job',
        'request_data' => $jobData
    ]);
    
    // Validate
    if (!$record->validate()) {
        return $record; // Already rejected
    }
    
    // Start processing
    $record->startProcessing(Auth::user());
    
    // Check if approval needed
    if ($this->requiresApproval($jobData)) {
        $record->requires_approval = true;
        $record->save();
        return $record; // Waiting for approval
    }
    
    // Auto-approve if no risk
    $record->bypassApproval();
    
    // Execute
    try {
        $job = Job::create($jobData);
        $record->execute(Auth::user());
        $record->entity_id = $job->id;
        $record->save();
        
        // Signal emission happens in execute()
        return $record;
    } catch (Exception $e) {
        $record->handleExecutionFailure($e);
        return $record;
    }
}
```

---

## CONCLUSION

ProcessRecord is the universal lifecycle engine. It ensures:

- **Consistency:** All actions follow same state machine
- **Auditability:** Every transition logged
- **Reversibility:** Rollback via Rewind engine
- **Governance:** AEGIS enforcement at every stage
- **Resilience:** Automatic retry, manual escalation

This single table replaces scattered approval logic, audit trails, and state management across 50+ controllers in pre-Nexus systems.

