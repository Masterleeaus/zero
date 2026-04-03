# Titan Signal Engine: Phase 1 Sprint Breakdown

**Duration**: Weeks 1-4 (20 working days)  
**Phase Goal**: Establish complete recording, emission, state management, and audit foundations  
**Team Size**: 1-2 developers  

---

## Sprint Structure

```
Sprint 1 (5 days) → Foundation: ProcessRecorder & ProcessStateMachine
Sprint 2 (5 days) → Audit Trail & Queue Infrastructure
Sprint 3 (5 days) → Integration & Testing
Sprint 4 (5 days) → Documentation & Hardening
```

---

## SPRINT 1: Foundation (Days 1-5)

### Day 1: Setup & ProcessRecorder Enhancement

**Morning (3 hours)**

**Task 1.1**: Create ProcessRecorder enhanced methods  
**File**: `app/Titan/Signals/ProcessRecorder.php`

**Checklist**:
- [ ] Add `recordFromVoice(string $transcript, int $userId): array` method
- [ ] Add `recordFromUI(array $formData, int $userId): array` method
- [ ] Implement `buildContext()` to capture user context (role, permissions, device, location, network)
- [ ] Create helper methods:
  - `parseVoiceIntent()` → stub for now, will integrate CreatiCore
  - `getUserRole(int $userId): string`
  - `getUserPermissions(int $userId): array`

**Code scaffold**:
```php
class ProcessRecorder
{
    public function __construct(
        private SignalNormalizer $normalizer,
        private SignalEmitter $emitter,
    ) {}

    public function recordFromVoice(string $transcript, int $userId): array
    {
        // 1. Parse intent
        $intent = $this->parseVoiceIntent($transcript, $userId);
        
        // 2. Create process
        $process = [
            'id' => 'proc-'.str_replace('.', '-', uniqid('', true)),
            'user_id' => $userId,
            'entity_type' => $intent['entity_type'],
            'domain' => $intent['domain'],
            'originating_node' => 'field-pwa',
            'current_state' => 'initiated',
            'data' => $intent['extracted_data'] ?? [],
            'context' => $this->buildContext($userId, 'field-pwa'),
            'created_at' => now(),
        ];
        
        // 3. Store
        DB::table('tz_processes')->insert($process);
        
        return [
            'process_id' => $process['id'],
            'status' => 'recorded_from_voice',
            'next_step' => 'confirm_details',
        ];
    }

    public function recordFromUI(array $formData, int $userId): array
    {
        // Validate form has required entity_type, domain
        // Build context
        // Store process
        // Return response
    }

    private function buildContext(int $userId, string $originatingNode): array
    {
        return [
            'user_role' => $this->getUserRole($userId),
            'user_permissions' => $this->getUserPermissions($userId),
            'device_capabilities' => $this->getDeviceCapabilities($originatingNode),
            'network_status' => $this->getNetworkStatus(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    private function parseVoiceIntent(string $transcript, int $userId): array
    {
        // STUB: Will integrate with CreatiCore/Claude for NLP
        // For now, return basic structure
        return [
            'entity_type' => 'generic',
            'domain' => 'general',
            'extracted_data' => ['raw_transcript' => $transcript],
        ];
    }

    private function getUserRole(int $userId): string
    {
        // Query users table for role (field-tech, manager, admin, etc)
        return 'field-tech'; // stub
    }

    private function getUserPermissions(int $userId): array
    {
        // Query permissions; stub for now
        return ['create_booking', 'submit_booking'];
    }

    private function getDeviceCapabilities(string $originatingNode): array
    {
        $map = [
            'field-pwa' => ['voice', 'offline', 'gps'],
            'web-pwa' => ['web', 'camera'],
            'mobile-app' => ['voice', 'offline', 'gps', 'camera'],
            'server' => ['api'],
        ];
        return $map[$originatingNode] ?? ['api'];
    }

    private function getNetworkStatus(): string
    {
        return app(NetworkDetector::class)->status(); // stub
    }
}
```

**Acceptance**:
- [ ] Voice recording method exists and logs process
- [ ] UI recording method exists and logs process
- [ ] Context building captures all required fields
- [ ] Process stored in tz_processes with proper state

**Time estimate**: 2-3 hours  
**Complexity**: Low (mostly scaffolding)

---

**Afternoon (2 hours)**

**Task 1.2**: Start ProcessStateMachine state validation  
**File**: `app/Titan/Signals/ProcessStateMachine.php`

**Checklist**:
- [ ] Define `VALID_TRANSITIONS` constant with full state graph
- [ ] Implement `transitionState(string $processId, string $newState): array` with validation
- [ ] Add transition logging to tz_process_states table
- [ ] Create basic test for valid/invalid transitions

**Code scaffold**:
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

    public function transitionState(
        string $processId,
        string $newState,
        ?array $metadata = null
    ): array {
        // 1. Get current process
        $process = DB::table('tz_processes')->find($processId);
        if (!$process) {
            throw new ProcessNotFoundException($processId);
        }

        // 2. Validate transition
        $allowed = self::VALID_TRANSITIONS[$process->current_state] ?? [];
        if (!in_array($newState, $allowed)) {
            throw new InvalidTransitionException(
                "Cannot transition from {$process->current_state} to {$newState}"
            );
        }

        // 3. Record transition in audit table
        DB::table('tz_process_states')->insert([
            'process_id' => $processId,
            'from_state' => $process->current_state,
            'to_state' => $newState,
            'metadata' => json_encode($metadata),
            'created_at' => now(),
        ]);

        // 4. Update process
        DB::table('tz_processes')->update([
            'current_state' => $newState,
            'updated_at' => now(),
        ]);

        return [
            'process_id' => $processId,
            'old_state' => $process->current_state,
            'new_state' => $newState,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
```

**Acceptance**:
- [ ] Transitions validate against VALID_TRANSITIONS
- [ ] Invalid transitions throw clear errors
- [ ] All transitions logged in tz_process_states
- [ ] Process.current_state updated correctly

**Time estimate**: 2 hours  
**Complexity**: Low-Medium

---

### Day 2: Complete ProcessStateMachine & Enhance Signal

**Morning (3 hours)**

**Task 2.1**: Finish ProcessStateMachine with notifications & state-change signals  
**File**: `app/Titan/Signals/ProcessStateMachine.php`

**Checklist**:
- [ ] Add `emitStateChangeSignal()` to notify automation engine
- [ ] Add `notifyStateChange()` stub (will integrate with notification system)
- [ ] Create `getTransitionPath(string $fromState, string $toState): array` to suggest transitions
- [ ] Add method `isValidTransition(string $from, string $to): bool`

**Code addition**:
```php
private function emitStateChangeSignal(string $processId, string $newState): void
{
    // Create signal for automation engine
    $signal = [
        'id' => 'sig-'.str_replace('.', '-', uniqid('', true)),
        'type' => 'process.state-changed',
        'kind' => 'state-transition',
        'severity' => 'info',
        'title' => 'Process State Changed',
        'process_id' => $processId,
        'new_state' => $newState,
        'payload' => ['event' => 'state_transition'],
        'source' => 'ProcessStateMachine',
        'origin' => 'internal',
        'timestamp' => now()->toIso8601String(),
    ];
    
    // Queue for broadcast
    DB::table('tz_signal_queue')->insert($signal);
}

private function notifyStateChange(string $processId, string $newState): void
{
    // STUB: Will integrate with notification service
    // Get process owner and notify
    // For now, log
    Log::info("Process {$processId} transitioned to {$newState}");
}

public function getTransitionPath(string $fromState, string $toState): array
{
    // BFS to find shortest path from -> to
    // Used for UI suggestions
}

public function isValidTransition(string $from, string $to): bool
{
    $allowed = self::VALID_TRANSITIONS[$from] ?? [];
    return in_array($to, $allowed);
}
```

**Acceptance**:
- [ ] State-change signals queued for broadcast
- [ ] Notification stubs in place
- [ ] Transition path helper works
- [ ] Validation helper works

**Time estimate**: 2-3 hours  
**Complexity**: Medium

---

**Afternoon (2 hours)**

**Task 2.2**: Expand Signal.php with validation/approval/processing fields  
**File**: `app/Titan/Signals/Signal.php`

**Checklist**:
- [ ] Add properties for validation results (status, errors, warnings)
- [ ] Add properties for approval chain (approvers, nextApprover, approvedBy)
- [ ] Add properties for processing (processedEntityId, processedAt)
- [ ] Add properties for rewind (rewindFrom, rolledBackBy)
- [ ] Update `toArray()` to include all new fields
- [ ] Create `fromProcess()` static factory method

**Code changes**:
```php
final class Signal
{
    public function __construct(
        public string $id,
        public string $type,
        public string $kind,
        public string $severity,
        public string $title,
        public ?string $body = null,
        public ?int $companyId = null,
        public ?int $teamId = null,
        public ?int $userId = null,
        public ?string $processId = null,  // NEW
        public array $payload = [],
        public array $meta = [],
        public ?string $source = null,
        public ?string $origin = null,
        public ?string $status = 'new',
        
        // Validation fields (NEW)
        public ?string $validationStatus = 'pending',
        public array $validationErrors = [],
        public array $validationWarnings = [],
        
        // Approval fields (NEW)
        public array $approvalChain = [],
        public ?int $nextApproverId = null,
        public array $approvedBy = [],
        
        // Processing fields (NEW)
        public ?int $processedEntityId = null,
        public ?string $processedAt = null,
        
        // Rewind fields (NEW)
        public ?string $rewindFrom = null,
        public ?string $rolledBackBy = null,
        
        public ?string $timestamp = null,
        public ?string $sourceEngine = null,
    ) {
        $this->timestamp ??= now()->toIso8601String();
    }

    // NEW: Factory from process record
    public static function fromProcess(array $process): self
    {
        return new self(
            id: 'sig-'.str_replace('.', '-', uniqid('', true)),
            type: $process['entity_type'].'.created',
            kind: 'process',
            severity: 'info',
            title: ucfirst($process['entity_type']).' created',
            processId: $process['id'],
            payload: $process['data'] ?? [],
            meta: $process['context'] ?? [],
            source: $process['originating_node'],
            origin: 'process_recorder',
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'kind' => $this->kind,
            'severity' => $this->severity,
            'title' => $this->title,
            'body' => $this->body,
            'company_id' => $this->companyId,
            'team_id' => $this->teamId,
            'user_id' => $this->userId,
            'process_id' => $this->processId,
            'payload' => $this->payload,
            'meta' => $this->meta,
            'source' => $this->source,
            'origin' => $this->origin,
            'status' => $this->status,
            'validation_status' => $this->validationStatus,
            'validation_errors' => $this->validationErrors,
            'validation_warnings' => $this->validationWarnings,
            'approval_chain' => $this->approvalChain,
            'next_approver_id' => $this->nextApproverId,
            'approved_by' => $this->approvedBy,
            'processed_entity_id' => $this->processedEntityId,
            'processed_at' => $this->processedAt,
            'rewind_from' => $this->rewindFrom,
            'rolled_back_by' => $this->rolledBackBy,
            'timestamp' => $this->timestamp,
            'source_engine' => $this->sourceEngine,
        ];
    }
}
```

**Acceptance**:
- [ ] All new properties added
- [ ] `toArray()` includes all fields
- [ ] `fromProcess()` factory creates signals from processes
- [ ] No breaking changes to existing code

**Time estimate**: 2 hours  
**Complexity**: Low

---

### Day 3: Audit Trail & Queue Infrastructure

**Morning (3 hours)**

**Task 3.1**: Create AuditTrail service and table  
**File**: 
- `app/Titan/Signals/AuditTrail.php`
- `database/schemas/tz_audit_log.sql` (or migration)

**Checklist**:
- [ ] Create migration: `create_tz_audit_log_table.php`
- [ ] Build AuditTrail service class
- [ ] Implement `recordEntry()` with full metadata
- [ ] Implement `getHistory()` to retrieve audit trail
- [ ] Implement `getTimeline()` for human-readable output
- [ ] Add indexes on (process_id, created_at) and (action, created_at)

**Migration template**:
```php
Schema::create('tz_audit_log', function (Blueprint $table) {
    $table->id();
    $table->string('process_id', 80);
    $table->string('signal_id', 80)->nullable();
    $table->string('action', 80); // validated|approved|rejected|rewound
    $table->unsignedBigInteger('performed_by')->nullable();
    $table->json('details')->nullable();
    $table->timestamps();
    
    $table->index(['process_id', 'created_at']);
    $table->index(['action', 'created_at']);
    $table->index(['performed_by', 'created_at']);
});
```

**Service class**:
```php
class AuditTrail
{
    public function recordEntry(
        string $processId,
        string $action,
        array $details = [],
        ?int $performedBy = null
    ): void {
        DB::table('tz_audit_log')->insert([
            'process_id' => $processId,
            'action' => $action,
            'details' => json_encode($details),
            'performed_by' => $performedBy ?? Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getHistory(string $processId): array
    {
        return DB::table('tz_audit_log')
            ->where('process_id', $processId)
            ->orderBy('created_at')
            ->get()
            ->map(fn($row) => (array)$row)
            ->all();
    }

    public function getTimeline(string $processId): string
    {
        $entries = $this->getHistory($processId);
        
        $timeline = "=== Process Timeline: {$processId} ===\n\n";
        
        foreach ($entries as $entry) {
            $time = $entry['created_at'];
            $action = $entry['action'];
            $timeline .= "{$time} → {$action}\n";
            
            if ($entry['details']) {
                $timeline .= "  Details: {$entry['details']}\n";
            }
        }
        
        return $timeline;
    }
}
```

**Acceptance**:
- [ ] Migration runs without errors
- [ ] AuditTrail service can record entries
- [ ] Can retrieve full history for a process
- [ ] Timeline output is human-readable
- [ ] Indexes created for query performance

**Time estimate**: 3 hours  
**Complexity**: Low

---

**Afternoon (2 hours)**

**Task 3.2**: Create signal queue table and basic processor  
**File**:
- `database/schemas/tz_signal_queue.sql`
- `app/Titan/Signals/QueueProcessor.php` (stub for phase 4)

**Migration**:
```php
Schema::create('tz_signal_queue', function (Blueprint $table) {
    $table->id();
    $table->string('signal_id', 80);
    $table->json('payload');
    $table->timestamp('broadcast_at')->nullable();
    $table->string('broadcast_status', 32)->default('pending'); // pending|sent|failed
    $table->integer('retry_count')->default(0);
    $table->timestamps();
    
    $table->index(['broadcast_status', 'created_at']);
    $table->index(['signal_id']);
});
```

**Stub processor**:
```php
class QueueProcessor
{
    public function processPendingQueue(): void
    {
        // STUB for Phase 4
        // Will be implemented with offline sync
        Log::info('Queue processor stub (Phase 4)');
    }

    public function markSent(int $queueId): void
    {
        DB::table('tz_signal_queue')
            ->where('id', $queueId)
            ->update(['broadcast_status' => 'sent', 'broadcast_at' => now()]);
    }

    public function incrementRetry(int $queueId): void
    {
        DB::table('tz_signal_queue')
            ->where('id', $queueId)
            ->increment('retry_count');
    }
}
```

**Acceptance**:
- [ ] Migration creates table with proper schema
- [ ] QueueProcessor stub exists
- [ ] Can mark signals as sent
- [ ] Can track retry attempts
- [ ] Indexes support queue queries

**Time estimate**: 2 hours  
**Complexity**: Low

---

### Day 4: Integration & First Test

**Morning (3 hours)**

**Task 4.1**: Integrate ProcessRecorder → ProcessStateMachine → AuditTrail  
**File**: `app/Titan/Signals/SignalsService.php`

**Update the ingest flow**:
```php
class SignalsService
{
    public function __construct(
        private readonly ProcessRecorder $recorder,
        private readonly ProcessStateMachine $stateMachine,
        private readonly SignalNormalizer $normalizer,
        private readonly AuditTrail $auditTrail,
    ) {}

    public function ingestFromVoice(string $transcript, int $userId): array
    {
        // 1. Record process from voice
        $processResult = $this->recorder->recordFromVoice($transcript, $userId);
        $processId = $processResult['process_id'];

        // 2. Transition state: initiated → signal-queued
        $this->stateMachine->transitionState($processId, 'signal-queued', [
            'origin' => 'voice',
            'transcript' => $transcript,
        ]);

        // 3. Record in audit trail
        $this->auditTrail->recordEntry($processId, 'voice_recorded', [
            'transcript' => $transcript,
        ], $userId);

        // 4. Create signal from process
        $process = DB::table('tz_processes')->find($processId);
        $signal = Signal::fromProcess((array)$process);

        // 5. Queue signal
        DB::table('tz_signal_queue')->insert([
            'signal_id' => $signal->id,
            'payload' => json_encode($signal->toArray()),
            'broadcast_status' => 'pending',
            'created_at' => now(),
        ]);

        return [
            'process_id' => $processId,
            'signal_id' => $signal->id,
            'status' => 'queued_for_validation',
        ];
    }

    public function ingestFromUI(array $formData, int $userId): array
    {
        // Similar flow but from UI
        $processResult = $this->recorder->recordFromUI($formData, $userId);
        $processId = $processResult['id'];

        $this->stateMachine->transitionState($processId, 'signal-queued', [
            'origin' => 'web',
        ]);

        $this->auditTrail->recordEntry($processId, 'form_submitted', [
            'form_fields' => array_keys($formData),
        ], $userId);

        // Continue with signal creation...
    }
}
```

**Acceptance**:
- [ ] Voice ingest creates process and transitions state
- [ ] UI ingest creates process and transitions state
- [ ] Audit trail logs each step
- [ ] Signals queued for validation
- [ ] No errors in flow

**Time estimate**: 3 hours  
**Complexity**: Medium

---

**Afternoon (2 hours)**

**Task 4.2**: Create integration test for Phase 1 flow  
**File**: `tests/Unit/TitanSignals/Phase1IntegrationTest.php`

**Test cases**:
```php
class Phase1IntegrationTest extends TestCase
{
    /** @test */
    public function voice_input_creates_process_and_queues_signal()
    {
        // 1. Input voice transcript
        $service = app(SignalsService::class);
        $result = $service->ingestFromVoice('Book a cleaning tomorrow', userId: 1);

        // 2. Assert process created
        $this->assertDatabaseHas('tz_processes', [
            'id' => $result['process_id'],
            'current_state' => 'signal-queued',
        ]);

        // 3. Assert state transition logged
        $this->assertDatabaseHas('tz_process_states', [
            'process_id' => $result['process_id'],
            'to_state' => 'signal-queued',
        ]);

        // 4. Assert audit entry
        $this->assertDatabaseHas('tz_audit_log', [
            'process_id' => $result['process_id'],
            'action' => 'voice_recorded',
        ]);

        // 5. Assert signal queued
        $this->assertDatabaseHas('tz_signal_queue', [
            'signal_id' => $result['signal_id'],
            'broadcast_status' => 'pending',
        ]);
    }

    /** @test */
    public function invalid_state_transition_throws_exception()
    {
        $machine = app(ProcessStateMachine::class);
        $processId = 'proc-test';

        // Create process in 'initiated' state
        DB::table('tz_processes')->insert([
            'id' => $processId,
            'company_id' => 1,
            'current_state' => 'initiated',
            'created_at' => now(),
        ]);

        // Try invalid transition
        $this->expectException(InvalidTransitionException::class);
        $machine->transitionState($processId, 'processing'); // Invalid from 'initiated'
    }

    /** @test */
    public function audit_trail_records_all_actions()
    {
        $auditTrail = app(AuditTrail::class);
        $processId = 'proc-test';

        $auditTrail->recordEntry($processId, 'created', ['data' => 'test']);
        $auditTrail->recordEntry($processId, 'validated', ['errors' => []]);
        $auditTrail->recordEntry($processId, 'approved', ['approver_id' => 5]);

        $history = $auditTrail->getHistory($processId);

        $this->assertCount(3, $history);
        $this->assertEquals('created', $history[0]['action']);
        $this->assertEquals('validated', $history[1]['action']);
        $this->assertEquals('approved', $history[2]['action']);
    }
}
```

**Acceptance**:
- [ ] All tests pass
- [ ] Integration flow works end-to-end
- [ ] Data correctly stored in all tables
- [ ] No errors or warnings

**Time estimate**: 2 hours  
**Complexity**: Medium

---

### Day 5: Testing, Documentation & Refinement

**Morning (3 hours)**

**Task 5.1**: Run full test suite and fix issues  

**Checklist**:
- [ ] All unit tests pass
- [ ] All integration tests pass
- [ ] No database errors
- [ ] Check for missing migrations
- [ ] Verify all tables created with correct schema
- [ ] Test with sample data (10 processes, 50 state transitions)
- [ ] Check performance (migrations complete < 2 seconds)

**Command**: `php artisan test --filter=Phase1`

**Acceptance**:
- [ ] All tests passing
- [ ] No warnings or deprecations
- [ ] Performance acceptable

**Time estimate**: 2 hours

---

**Afternoon (2 hours)**

**Task 5.2**: Write Phase 1 API Documentation  
**File**: `docs/PHASE_1_API.md`

**Document**:
- [ ] Record from voice: `POST /api/processes/record-voice`
- [ ] Record from UI: `POST /api/processes/record-ui`
- [ ] Get process: `GET /api/processes/{id}`
- [ ] Get audit history: `GET /api/processes/{id}/audit`
- [ ] Transition state: `POST /api/processes/{id}/transition`

**Example endpoints**:
```
POST /api/processes/record-voice
{
  "transcript": "Book a cleaning tomorrow at 10am",
  "user_id": 1,
  "company_id": 1
}

Response:
{
  "process_id": "proc-abc123",
  "signal_id": "sig-xyz789",
  "status": "queued_for_validation",
  "next_step": "validation"
}
```

**Acceptance**:
- [ ] All endpoints documented
- [ ] Request/response examples provided
- [ ] Error codes documented
- [ ] Ready for developer consumption

**Time estimate**: 2 hours

---

## Sprint 1 Summary

| Task | Status | Owner | Days |
|------|--------|-------|------|
| ProcessRecorder enhance | ✓ | Dev 1 | 1.5 |
| ProcessStateMachine | ✓ | Dev 1 | 1.5 |
| Signal expansion | ✓ | Dev 1 | 1 |
| AuditTrail service | ✓ | Dev 1 | 1.5 |
| Queue infrastructure | ✓ | Dev 1 | 1 |
| Integration flow | ✓ | Dev 1-2 | 1.5 |
| Testing | ✓ | Dev 2 | 1 |
| Documentation | ✓ | Dev 1-2 | 1 |

**Total**: 10 days (half of Phase 1)

---

## SPRINT 2: Audit, Queue & Advanced Recording (Days 6-10)

### Day 6: Enhanced Context Capture

**Task 6.1**: Implement full context builders  
**File**: `app/Titan/Signals/ContextBuilder.php`

**Features**:
- Device capabilities detection
- Network status checking
- User location capture (with privacy)
- Permission inference
- Role hierarchy loading

**Time estimate**: 2 days

---

### Day 7-8: Voice Intent Parsing

**Task 7.1**: Integrate CreatiCore for voice intent extraction  
**File**: `app/Titan/Signals/VoiceIntentParser.php`

**Features**:
- Call CreatiCore/Claude to parse transcript
- Extract entity_type (booking, invoice, job, etc)
- Extract domain (jobs, invoices, customers)
- Extract relevant data fields
- Fallback to keyword-based parsing if AI unavailable

**Time estimate**: 2 days

---

### Day 9: Permissions & Role System

**Task 9.1**: Build permission validation  
**File**: `app/Titan/Signals/PermissionChecker.php`

**Features**:
- Load user roles
- Get role permissions
- Check if user can perform action
- Cache for performance

**Time estimate**: 1 day

---

### Day 10: Hardening & Review

**Task 10.1**: Final Sprint 2 review  
- [ ] All new features tested
- [ ] Integration tests passing
- [ ] Performance acceptable (< 100ms per process record)
- [ ] Ready for Phase 2 (Validation)

---

## SPRINT 3 & 4: Integration, Testing, Documentation

See main upgrade plan for detailed breakdown.

---

## Git Workflow for Phase 1

### Branch structure:
```
main
├── develop
│   ├── feature/process-recorder
│   ├── feature/state-machine
│   ├── feature/audit-trail
│   ├── feature/queue-infrastructure
│   └── feature/integration-flow
```

### Commit messages:
```
[Phase1] Enhance ProcessRecorder with voice/UI paths
[Phase1] Implement complete ProcessStateMachine with validation
[Phase1] Add AuditTrail service and audit_log table
[Phase1] Integrate recording → state → signal flow
[Phase1] Add Phase 1 integration tests
[Phase1] Document Phase 1 API endpoints
```

### Merge strategy:
- Feature branch → develop (PR review)
- Develop → main (after full Phase 1 completion)
- Tag: `v1.0-phase1-complete`

---

## Success Criteria: Phase 1

✓ **Process Recording**: Voice and UI inputs → tz_processes  
✓ **State Machine**: Transitions validated; invalid transitions rejected  
✓ **Signal Emission**: Processes → Signals; queued in tz_signal_queue  
✓ **Audit Trail**: Every action logged in tz_audit_log with full metadata  
✓ **Integration**: Full flow tested end-to-end  
✓ **Documentation**: API documented; ready for Phase 2 developers  
✓ **Performance**: < 100ms per process record; migrations < 2 seconds  
✓ **Test Coverage**: Unit + integration tests; all passing  

**Phase 1 Complete**: Foundation established for validation & approval layers (Phase 2)

---

**Document Version**: 1.0  
**Last Updated**: March 31, 2025  
**Next Milestone**: Sprint 1 standup (Week 1, Monday 9am)
