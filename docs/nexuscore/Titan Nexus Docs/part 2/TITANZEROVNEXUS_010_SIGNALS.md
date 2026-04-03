# TITANZEROVNEXUS: Signal Spine & Event Architecture

**Version:** 5.0  
**Document:** 010  
**Status:** Complete Signal System Specification

---

## EXECUTIVE SUMMARY

The **Signal Spine** is the nervous system of Nexus. Every lifecycle transition (job.created, invoice.paid, campaign.launched) emits a signal. Subscribers listen and trigger automation.

One unified signal registry replaces 40+ scattered event systems.

---

## I. SIGNAL ARCHITECTURE OVERVIEW

```
ProcessRecord State Transition
    ↓
Signal Emission
    ├─ Signal name (work.job.scheduled)
    ├─ Entity ID & type (job_456, 'job')
    ├─ Data payload {scheduled_at, company_id, ...}
    └─ Metadata {user_id, timestamp, mode, ...}
    ↓
Signal Registry (immutable log)
    ├─ Record signal forever
    ├─ Enable event sourcing
    └─ Enable Rewind replay
    ↓
Subscriber Dispatch (async)
    ├─ Find all subscribers to this signal
    ├─ Trigger in parallel (safe due to Nexus schema)
    └─ Retry on failure (exponential backoff)
    ↓
Subscriber Actions
    ├─ Auto-invoice creation
    ├─ Send notifications
    ├─ Update forecasts
    └─ Trigger next workflow
```

---

## II. SIGNAL REGISTRY TABLE

```sql
CREATE TABLE tz_signals (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    
    -- Signal identity
    signal_name VARCHAR(100) NOT NULL, -- 'work.job.completed'
    signal_type ENUM('lifecycle', 'error', 'alert'),
    mode VARCHAR(50) NOT NULL, -- Which mode emitted this
    
    -- Entity context
    entity_id BIGINT,
    entity_type VARCHAR(50),
    parent_entity_id BIGINT,
    
    -- Data payload
    payload JSON, -- {scheduled_at, amount, title, ...}
    metadata JSON, -- {user_id, ip, source, ...}
    
    -- Audit
    emitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    emitted_by BIGINT, -- Which Sentinel
    
    -- Rewind integration
    process_record_id BIGINT,
    rewind_checkpoint_id BIGINT,
    
    -- Immutability
    INDEX idx_signal_name (signal_name),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_emitted_at (emitted_at),
    
    UNIQUE KEY uk_signal_entity (signal_name, entity_id, emitted_at)
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE tz_signal_events (
    id BIGINT PRIMARY KEY,
    signal_id BIGINT NOT NULL,
    
    -- Subscription tracking
    subscriber_id BIGINT,
    subscriber_class VARCHAR(255),
    
    -- Execution
    executed_at TIMESTAMP,
    result JSON, -- What subscriber returned
    error TEXT, -- If subscriber failed
    retry_count INT DEFAULT 0,
    
    FOREIGN KEY (signal_id) REFERENCES tz_signals(id),
    INDEX idx_subscriber (subscriber_id),
    INDEX idx_executed (executed_at)
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE tz_signal_subscribers (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    
    -- Subscription
    signal_pattern VARCHAR(100), -- 'work.job.*' or 'work.job.completed'
    subscriber_class VARCHAR(255), -- 'App\Subscribers\InvoiceSubscriber'
    
    -- Configuration
    async BOOLEAN DEFAULT TRUE,
    retry_policy VARCHAR(50), -- 'exponential', 'linear', 'none'
    max_retries INT DEFAULT 3,
    
    -- Control
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    
    UNIQUE (tenant_id, signal_pattern, subscriber_class),
    INDEX idx_pattern (signal_pattern),
    INDEX idx_enabled (enabled)
) ENGINE=InnoDB CHARSET=utf8mb4;
```

---

## III. SIGNAL TYPES & NAMING CONVENTION

### **Lifecycle Signals (Primary)**

Emitted when ProcessRecord transitions:

```
work.job.scheduled          (job created)
work.job.started            (work began)
work.job.completed          (work finished)
work.job.failed             (work failed)
work.job.rescheduled        (job rescheduled)

channel.message.received    (incoming message)
channel.message.sent        (outgoing message)
channel.conversation.escalated

money.invoice.created       (invoice generated)
money.invoice.sent          (invoice delivered)
money.payment.received      (payment recorded)
money.invoice.overdue       (payment past due)
money.invoice.paid          (fully paid)

growth.campaign.launched    (campaign started)
growth.post.published       (post went live)
growth.engagement.recorded  (interaction tracked)
growth.lead.converted       (prospect → customer)
growth.rebook.suggested     (rebook offered)

admin.permission.granted    (user elevated)
admin.permission.revoked    (user demoted)
admin.extension.installed   (module added)
admin.policy.updated        (rules changed)
```

### **Error Signals (Secondary)**

Emitted when something fails:

```
work.job.execution_failed      (couldn't create)
money.invoice.creation_failed  (invoice error)
growth.campaign.launch_failed  (campaign rejected)

Generic:
process.validation_failed      (prerequisite check)
process.permission_denied      (auth failed)
process.approval_required      (waiting for approval)
process.escalation_needed      (needs human)
```

### **Alert Signals (Tertiary)**

Emitted when thresholds exceeded:

```
work.job.overdue             (not completed on time)
money.invoice.payment_delayed (partial payment)
growth.campaign.low_engagement (few responses)
system.cost_anomaly          (spending spike)
```

---

## IV. SIGNAL EMISSION

### **In a Sentinel**

```php
// After successful execution
$record->execute(Auth::user());

// Signal is emitted automatically via ProcessRecord
Signal::emit('work.job.completed', [
    'entity_id' => $job->id,
    'entity_type' => 'job',
    'payload' => [
        'job_number' => $job->job_number,
        'customer_id' => $job->customer_id,
        'completed_at' => now(),
        'evidence_count' => count($evidence)
    ],
    'metadata' => [
        'user_id' => Auth::id(),
        'ip_address' => request()->ip(),
        'source' => 'mobile_app'
    ]
]);
```

### **Signal Emission Flow**

```php
// In Signal facade
class Signal {
    public static function emit(string $signalName, array $data): void {
        // 1. Create immutable signal record
        $signal = tz_signals::create([
            'signal_name' => $signalName,
            'entity_id' => $data['entity_id'],
            'entity_type' => $data['entity_type'],
            'payload' => $data['payload'],
            'metadata' => $data['metadata'],
            'emitted_at' => now(),
            'emitted_by' => Auth::id()
        ]);
        
        // 2. Queue subscriber dispatch
        DispatchSignalSubscribers::dispatch($signal)->onQueue('signals');
        
        // 3. Update Rewind checkpoint
        if (isset($data['process_record_id'])) {
            RewindCheckpoint::recordSignal($signal);
        }
        
        // 4. Log to audit trail
        AuditLog::record("Signal emitted: $signalName");
    }
}
```

---

## V. SIGNAL SUBSCRIBERS

### **Example 1: Auto-Invoice Subscriber**

When a job completes, automatically create an invoice:

```php
namespace App\Subscribers;

use TitanZero\Nexus\Subscribers\BaseSubscriber;
use TitanZero\Nexus\Events\Signal;

class AutoInvoiceSubscriber extends BaseSubscriber {
    
    public function handle(Signal $signal): void {
        // Only listen to job.completed
        if ($signal->name !== 'work.job.completed') {
            return;
        }
        
        $job = Entity::find($signal->entity_id);
        
        // Check if already invoiced
        if ($job->relationshipTo('invoice', 'references')->exists()) {
            return; // Already invoiced
        }
        
        // Auto-create invoice
        $lineItems = [
            [
                'description' => $job->getAttribute('title'),
                'quantity' => 1,
                'unit_price' => $job->getAttribute('quoted_price') ?? 0,
                'tax_rate' => 0.10
            ]
        ];
        
        $result = Sentinel::for('money')->createInvoice(
            job: $job,
            lineItems: $lineItems
        );
        
        // Log result
        $this->logAction('auto_invoice', $signal->entity_id, [
            'invoice_id' => $result->data['invoice_id'] ?? null,
            'status' => $result->state
        ]);
    }
}

// Register in config/nexus.php
'subscribers' => [
    AutoInvoiceSubscriber::class => [
        'signals' => ['work.job.completed'],
        'async' => true,
        'retry_policy' => 'exponential'
    ]
]
```

### **Example 2: Notification Subscriber**

When something important happens, notify relevant people:

```php
class NotificationSubscriber extends BaseSubscriber {
    
    public function handle(Signal $signal): void {
        $notificationMap = [
            'work.job.completed' => [
                'roles' => ['manager', 'admin'],
                'channels' => ['email', 'slack'],
                'template' => 'job_completed'
            ],
            'money.payment.received' => [
                'roles' => ['accountant', 'admin'],
                'channels' => ['email'],
                'template' => 'payment_received'
            ],
            'growth.lead.converted' => [
                'roles' => ['sales', 'admin'],
                'channels' => ['slack', 'sms'],
                'template' => 'new_lead'
            ]
        ];
        
        $config = $notificationMap[$signal->name] ?? null;
        if (!$config) return;
        
        // Get users with relevant roles
        $users = User::whereHasRole($config['roles'])
            ->where('tenant_id', Auth::user()->tenant_id)
            ->get();
        
        foreach ($users as $user) {
            foreach ($config['channels'] as $channel) {
                Sentinel::for('channel')->sendMessage(
                    to: $this->getAddress($user, $channel),
                    channel: $channel,
                    content: $this->renderTemplate($config['template'], $signal)
                );
            }
        }
    }
    
    private function renderTemplate(string $template, Signal $signal): string {
        return match($template) {
            'job_completed' => "Job #{$signal->payload['job_number']} completed",
            'payment_received' => "Payment of \${$signal->payload['amount']} received",
            'new_lead' => "New lead: {$signal->payload['lead_name']}",
            default => 'Action completed'
        };
    }
}
```

### **Example 3: Forecast Update Subscriber**

When payment received or invoice created, update revenue forecast:

```php
class ForecastUpdateSubscriber extends BaseSubscriber {
    
    public function handle(Signal $signal): void {
        $signals = [
            'money.invoice.created',
            'money.payment.received',
            'money.invoice.paid'
        ];
        
        if (!in_array($signal->name, $signals)) {
            return;
        }
        
        // Get company
        $invoice = Entity::find($signal->entity_id);
        $company = Company::find($invoice->company_id);
        
        // Recalculate forecast
        $forecast = MoneySentinel::forecastRevenue($company, months: 12);
        
        // Cache forecast (for dashboard)
        cache()->put(
            "forecast:company_{$company->id}",
            $forecast,
            hours: 6
        );
    }
}
```

---

## VI. SIGNAL PATTERNS (Wildcards)

Subscribers can listen to patterns:

```php
// Listen to all job events
'signal_pattern' => 'work.job.*'

// Listen to all work events
'signal_pattern' => 'work.*'

// Listen to all events
'signal_pattern' => '*'

// Listen to failed events only
'signal_pattern' => '*.*.failed'

// Listen to created/updated/deleted
'signal_pattern' => '*.*.{created,updated,deleted}'
```

---

## VII. SIGNAL ORDERING & CAUSALITY

Signals maintain causal order (no causality violations):

```
T1: work.job.scheduled       (event A)
T2: work.job.started         (event B, caused by A)
T3: money.invoice.created    (event C, caused by B)
T4: channel.message.sent     (event D, caused by C)

Cannot happen:
T5: work.job.rescheduled     (would violate causality, job already started)

Enforced by:
├─ ProcessRecord state machine (prevents invalid transitions)
├─ Signal registry immutability (no retroactive changes)
└─ Rewind checkpoints (enable undo without breaking causality)
```

---

## VIII. SIGNAL DRIVEN ORCHESTRATION

Complex workflows triggered by signal chains:

```
Customer requests rebook:
    ↓ (channel.message.received: "reschedule")
Growth Sentinel suggests time slot
    ↓ (growth.rebook.suggested)
Auto-send confirmation message
    ↓ (channel.message.sent)
Customer confirms
    ↓ (channel.message.received: "yes")
Reschedule job
    ↓ (work.job.rescheduled)
Update invoice (if applicable)
    ↓ (money.invoice.updated)
Send technician notification
    ↓ (channel.message.sent)
End orchestration

All driven by signals, no hardcoded workflows!
```

---

## IX. SIGNAL REPLAY (Event Sourcing)

Rebuild state by replaying signals:

```php
class RebuildStateCommand extends Command {
    
    public function handle() {
        // Start from empty state
        DB::statement('TRUNCATE TABLE entities');
        DB::statement('TRUNCATE TABLE entity_attributes');
        DB::statement('TRUNCATE TABLE entity_relationships');
        
        // Replay all signals
        Signal::query()
            ->orderBy('emitted_at')
            ->chunk(1000, function($signals) {
                foreach ($signals as $signal) {
                    // Execute what would have happened
                    match($signal->signal_name) {
                        'work.job.scheduled' => $this->replayJobScheduled($signal),
                        'work.job.completed' => $this->replayJobCompleted($signal),
                        'money.invoice.created' => $this->replayInvoiceCreated($signal),
                        // ... etc
                    };
                }
            });
        
        $this->info('State rebuilt from signal log');
    }
}
```

---

## X. MONITORING & OBSERVABILITY

### **Signal Health Dashboard**

```sql
-- Most emitted signals
SELECT signal_name, COUNT(*) as count
FROM tz_signals
WHERE emitted_at > NOW() - INTERVAL 1 HOUR
GROUP BY signal_name
ORDER BY count DESC;

-- Slowest subscribers
SELECT 
    signal_name,
    subscriber_class,
    AVG(EXTRACT(EPOCH FROM (executed_at - created_at))) as avg_duration_sec
FROM tz_signal_events
WHERE executed_at > NOW() - INTERVAL 1 DAY
GROUP BY signal_name, subscriber_class
HAVING AVG(...) > 5
ORDER BY avg_duration_sec DESC;

-- Failing subscribers
SELECT 
    signal_name,
    subscriber_class,
    COUNT(*) as failures
FROM tz_signal_events
WHERE error IS NOT NULL
    AND executed_at > NOW() - INTERVAL 1 DAY
GROUP BY signal_name, subscriber_class
ORDER BY failures DESC;
```

---

## CONCLUSION

The Signal Spine transforms Nexus from a state machine into a **reactive system**.

Every action emits signals. Every signal triggers reactions. Workflows orchestrate through events, not hardcoded logic.

This enables:
- ✅ **Loose coupling** (subscribers don't know about each other)
- ✅ **Event sourcing** (replay to rebuild state)
- ✅ **Infinite extensibility** (add subscribers without changing code)
- ✅ **Observable workflows** (see exactly what happened, when, why)

One unified signal registry replaces 40+ scattered event systems.

