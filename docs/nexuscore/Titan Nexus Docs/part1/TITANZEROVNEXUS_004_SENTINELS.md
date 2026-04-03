# TITANZEROVNEXUS: Five Sentinels & Mode Architecture

**Version:** 5.0  
**Document:** 004  
**Status:** Production Specification

---

## EXECUTIVE SUMMARY

Each mode has one **Sentinel**: a class that embodies all domain authority for that mode. Five Sentinels, 2,000 lines total, replaces 150+ scattered controllers and services.

```
Work Mode       → WorkSentinel (execution)
Channel Mode    → ChannelSentinel (interaction)
Money Mode      → MoneySentinel (value)
Growth Mode     → GrowthSentinel (expansion)
Admin Mode      → AdminSentinel (governance)
```

Every action routes through the appropriate Sentinel. No other class can make domain decisions.

---

## I. SENTINEL BASE CLASS

```php
namespace App\Nexus\Sentinels;

abstract class BaseSentinel {
    
    protected string $mode;
    protected string $entityType; // 'job', 'invoice', 'campaign', etc.
    
    /**
     * Central execution method: all domain actions flow through here
     */
    protected function executeAction(
        string $actionName,
        array $prerequisites,
        callable $execute,
        string $signalName
    ): ProcessRecord {
        
        // 1. Create ProcessRecord to track this action
        $record = ProcessRecord::create([
            'tenant_id' => Auth::user()->tenant_id,
            'user_id' => Auth::id(),
            'mode' => $this->mode,
            'action_type' => $actionName,
            'state' => 'proposed',
            'intent' => "User executing: $actionName"
        ]);
        
        // 2. Validate prerequisites
        foreach ($prerequisites as $check) {
            if (!$check()) {
                $record->state = 'rejected';
                $record->save();
                Signal::emit("$this->mode.validation_failed", $record);
                throw new PrerequisiteException();
            }
        }
        
        // 3. Check permissions via AEGIS
        $record->startProcessing(Auth::user());
        
        if (!Gate::allows($actionName, Auth::user())) {
            $record->state = 'rejected';
            $record->save();
            Signal::emit("$this->mode.permission_denied", $record);
            throw new PermissionDeniedException();
        }
        
        // 4. Check if approval needed
        if ($this->requiresApproval($actionName)) {
            $record->requires_approval = true;
            $record->save();
            // Return and wait for approval
            return $record;
        }
        
        $record->bypassApproval();
        
        // 5. Execute domain logic
        try {
            $result = $execute();
            
            $record->execute(Auth::user());
            $record->execution_result = $result;
            $record->save();
            
            // 6. Emit signal for subscribers
            Signal::emit($signalName, $record);
            
            // 7. Mark completed when async tasks done
            // (or after a small delay for cascading triggers)
            $record->markCompleted();
            
            return $record;
            
        } catch (DomainException $e) {
            $record->handleExecutionFailure($e);
            throw $e;
        }
    }
    
    /**
     * Override in subclasses to define which actions require approval
     */
    protected function requiresApproval(string $actionName): bool {
        return false; // Default: no approval needed
    }
    
    /**
     * Query helper: all queries auto-scoped to tenant
     */
    protected function query(Model $model) {
        return $model
            ->where('tenant_id', Auth::user()->tenant_id);
    }
}
```

---

## II. WORKSENTINEL: EXECUTION AUTHORITY

Controls all operational actions.

```php
namespace App\Nexus\Sentinels;

class WorkSentinel extends BaseSentinel {
    
    protected string $mode = 'work';
    
    /**
     * Schedule a job
     */
    public function scheduleJob(Company $company, array $jobData): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'work.job.schedule',
            prerequisites: [
                fn() => Auth::user()->can('create_job'),
                fn() => $company->exists && $company->tenant_id === Auth::user()->tenant_id,
                fn() => isset($jobData['title']) && strlen($jobData['title']) > 0,
                fn() => isset($jobData['location_id']) && Location::find($jobData['location_id']),
                fn() => isset($jobData['scheduled_at']) && $jobData['scheduled_at'] > now(),
            ],
            execute: fn() => Entity::create([
                'tenant_id' => Auth::user()->tenant_id,
                'entity_type' => 'job',
                'entity_class' => 'App\Work\Job',
                'status' => 'proposed',
                'company_id' => $company->id,
                'created_by' => Auth::id()
            ])->addAttributes($jobData),
            signalName: 'work.job.scheduled'
        );
    }
    
    /**
     * Start a job
     */
    public function startJob(Job $job): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'work.job.start',
            prerequisites: [
                fn() => Auth::user()->can('start_job'),
                fn() => $job->status === 'proposed',
                fn() => $job->scheduled_at <= now(),
                fn() => !$job->hasUnresolvedIssues(),
            ],
            execute: fn() => $job->update(['status' => 'processing']),
            signalName: 'work.job.started'
        );
    }
    
    /**
     * Complete a job with evidence
     */
    public function completeJob(Job $job, array $evidence): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'work.job.complete',
            prerequisites: [
                fn() => Auth::user()->can('complete_job'),
                fn() => $job->status === 'processing',
                fn() => $job->checklist()->allItemsCompleted(),
                fn() => isset($evidence['photos']) && count($evidence['photos']) > 0,
            ],
            execute: function() use ($job, $evidence) {
                $job->update(['status' => 'executed']);
                
                // Attach evidence
                foreach ($evidence['photos'] as $photo) {
                    Evidence::create([
                        'entity_id' => $job->id,
                        'entity_type' => 'job',
                        'type' => 'photo',
                        'file_path' => $photo['path']
                    ]);
                }
                
                return ['job_id' => $job->id, 'evidence_count' => count($evidence['photos'])];
            },
            signalName: 'work.job.completed'
        );
    }
    
    /**
     * Fail a job (customer unavailable, needs rescheduling)
     */
    public function failJob(Job $job, string $reason): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'work.job.fail',
            prerequisites: [
                fn() => Auth::user()->can('fail_job'),
                fn() => in_array($job->status, ['processing', 'proposed']),
                fn() => strlen($reason) > 0,
            ],
            execute: fn() => $job->update(['status' => 'rejected']),
            signalName: 'work.job.failed'
        );
    }
    
    /**
     * Reschedule a job
     */
    public function rescheduleJob(Job $job, DateTime $newTime): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'work.job.reschedule',
            prerequisites: [
                fn() => Auth::user()->can('reschedule_job'),
                fn() => in_array($job->status, ['proposed', 'rejected']),
                fn() => $newTime > now()->addHours(1),
                fn() => $this->isTimeAvailable($job->location_id, $newTime),
            ],
            execute: fn() => $job->update(['scheduled_at' => $newTime]),
            signalName: 'work.job.rescheduled'
        );
    }
    
    /**
     * Assign a technician to a job
     */
    public function assignTechnician(Job $job, User $technician): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'work.job.assign_technician',
            prerequisites: [
                fn() => Auth::user()->can('assign_technician'),
                fn() => $technician->hasRole('technician'),
                fn() => !$technician->hasConflict($job->scheduled_at),
            ],
            execute: fn() => EntityRelationship::create([
                'from_entity_id' => $technician->id,
                'to_entity_id' => $job->entity_id,
                'relationship_type' => 'assigned_to'
            ]),
            signalName: 'work.technician.assigned'
        );
    }
    
    /**
     * Override: which actions need approval?
     */
    protected function requiresApproval(string $actionName): bool {
        // Simple actions don't need approval
        return false;
    }
    
    /**
     * Helper: check if time slot is available
     */
    private function isTimeAvailable(int $locationId, DateTime $time): bool {
        $slotDuration = 60; // minutes
        
        $conflict = Job::where('location_id', $locationId)
            ->where('status', 'processing')
            ->whereBetween('scheduled_at', [
                $time->copy()->subMinutes($slotDuration),
                $time->copy()->addMinutes($slotDuration)
            ])
            ->exists();
        
        return !$conflict;
    }
}
```

---

## III. CHANNELSENTINEL: INTERACTION AUTHORITY

Controls all communication.

```php
namespace App\Nexus\Sentinels;

class ChannelSentinel extends BaseSentinel {
    
    protected string $mode = 'channel';
    
    /**
     * Receive incoming message
     */
    public function receiveMessage(array $incomingData): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'channel.message.receive',
            prerequisites: [
                fn() => isset($incomingData['from']),
                fn() => isset($incomingData['channel']),
                fn() => in_array($incomingData['channel'], ['whatsapp', 'email', 'sms', 'api']),
            ],
            execute: function() use ($incomingData) {
                
                // Find or create conversation
                $conversation = Conversation::firstOrCreate([
                    'tenant_id' => Auth::user()->tenant_id,
                    'participant_id' => $this->identifyParticipant($incomingData['from']),
                    'channel' => $incomingData['channel']
                ]);
                
                // Create message entity
                $message = Entity::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'entity_type' => 'message',
                    'entity_class' => 'App\Channel\Message',
                    'parent_id' => $conversation->entity_id,
                    'status' => 'received'
                ])->addAttributes([
                    'content' => $incomingData['content'],
                    'channel' => $incomingData['channel'],
                    'from' => $incomingData['from'],
                    'received_at' => now()
                ]);
                
                // Route to appropriate mode (Envoy → Mode Decider)
                return ['message_id' => $message->id, 'conversation_id' => $conversation->id];
            },
            signalName: 'channel.message.received'
        );
    }
    
    /**
     * Send outgoing message
     */
    public function sendMessage(string $to, string $channel, string $content): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'channel.message.send',
            prerequisites: [
                fn() => Auth::user()->can('send_message'),
                fn() => strlen($content) > 0 && strlen($content) <= 4096,
                fn() => in_array($channel, ['whatsapp', 'email', 'sms']),
                fn() => $this->validateChannelAddress($to, $channel),
            ],
            execute: function() use ($to, $channel, $content) {
                
                // Create message entity
                $message = Entity::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'entity_type' => 'message',
                    'status' => 'sending'
                ])->addAttributes([
                    'to' => $to,
                    'channel' => $channel,
                    'content' => $content,
                    'created_by' => Auth::id()
                ]);
                
                // Send via appropriate adapter
                $adapter = $this->getChannelAdapter($channel);
                $result = $adapter->send($to, $content);
                
                $message->setAttribute('external_id', $result['message_id']);
                $message->setAttribute('status', 'sent');
                
                return $result;
            },
            signalName: 'channel.message.sent'
        );
    }
    
    /**
     * Escalate conversation to human
     */
    public function escalateConversation(Conversation $conversation, string $reason): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'channel.conversation.escalate',
            prerequisites: [
                fn() => Auth::user()->can('escalate_conversation'),
                fn() => $conversation->status !== 'escalated',
            ],
            execute: function() use ($conversation, $reason) {
                $conversation->update(['status' => 'escalated']);
                
                // Notify admin
                $admin = User::roles(['admin'])->first();
                Notification::send($admin, new ConversationEscalationNotification($conversation, $reason));
                
                return ['conversation_id' => $conversation->id];
            },
            signalName: 'channel.conversation.escalated'
        );
    }
    
    /**
     * Helper: identify participant from external ID
     */
    private function identifyParticipant(string $externalId): int {
        // Look up in participant registry
        // Or create new participant if not found
        $participant = Participant::firstOrCreate([
            'tenant_id' => Auth::user()->tenant_id,
            'external_id' => $externalId
        ]);
        
        return $participant->id;
    }
    
    /**
     * Helper: validate channel address format
     */
    private function validateChannelAddress(string $address, string $channel): bool {
        return match($channel) {
            'email' => filter_var($address, FILTER_VALIDATE_EMAIL),
            'whatsapp' => preg_match('/^\+?[1-9]\d{1,14}$/', $address),
            'sms' => preg_match('/^\+?[1-9]\d{1,14}$/', $address),
            default => false
        };
    }
    
    /**
     * Helper: get channel adapter
     */
    private function getChannelAdapter(string $channel): ChannelAdapter {
        return match($channel) {
            'email' => new SMTPAdapter(),
            'whatsapp' => new TwilioAdapter(),
            'sms' => new TwilioAdapter(),
            'api' => new DirectAdapter(),
        };
    }
}
```

---

## IV. MONEYSENTINEL: VALUE AUTHORITY

Controls all financial transactions.

```php
namespace App\Nexus\Sentinels;

class MoneySentinel extends BaseSentinel {
    
    protected string $mode = 'money';
    
    /**
     * Create an invoice
     */
    public function createInvoice(Job $job, Company $company, array $lineItems): ProcessRecord {
        
        $record = ProcessRecord::create([
            'tenant_id' => Auth::user()->tenant_id,
            'user_id' => Auth::id(),
            'mode' => 'money',
            'action_type' => 'money.invoice.create',
            'state' => 'proposed',
            'request_data' => ['job_id' => $job->id, 'line_items' => $lineItems]
        ]);
        
        // Check if approval needed (large invoice)
        $total = collect($lineItems)->sum('amount');
        if ($total > 5000) {
            $record->requires_approval = true;
            $record->save();
            return $record; // Waiting for approval
        }
        
        // No approval needed; execute
        $record->bypassApproval();
        
        try {
            $invoice = Entity::create([
                'tenant_id' => Auth::user()->tenant_id,
                'entity_type' => 'invoice',
                'status' => 'proposed',
                'company_id' => $company->id,
                'customer_id' => $job->customer_id
            ])->addAttributes([
                'job_id' => $job->id,
                'amount' => $total,
                'due_date' => now()->addDays(30),
                'created_by' => Auth::id()
            ]);
            
            // Add line items
            foreach ($lineItems as $item) {
                EntityAttribute::create([
                    'entity_id' => $invoice->id,
                    'attribute_name' => 'line_item_' . uniqid(),
                    'attribute_value' => json_encode($item)
                ]);
            }
            
            $record->execute(Auth::user());
            Signal::emit('money.invoice.created', $record);
            
            return $record;
        } catch (Exception $e) {
            $record->handleExecutionFailure($e);
            throw $e;
        }
    }
    
    /**
     * Record a payment
     */
    public function recordPayment(Invoice $invoice, array $paymentData): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'money.payment.record',
            prerequisites: [
                fn() => Auth::user()->can('record_payment'),
                fn() => $invoice->status !== 'paid',
                fn() => $paymentData['amount'] > 0,
                fn() => $paymentData['amount'] <= $invoice->getAttribute('amount'),
            ],
            execute: function() use ($invoice, $paymentData) {
                
                $payment = Entity::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'entity_type' => 'payment',
                    'parent_id' => $invoice->id,
                    'status' => 'recorded'
                ])->addAttributes($paymentData);
                
                // Update invoice status
                $remaining = $invoice->getAttribute('amount') - $paymentData['amount'];
                if ($remaining <= 0) {
                    $invoice->setAttribute('status', 'paid');
                } else {
                    $invoice->setAttribute('status', 'partial');
                }
                
                return ['payment_id' => $payment->id, 'invoice_status' => $invoice->status];
            },
            signalName: 'money.payment.received'
        );
    }
    
    /**
     * Mark invoice as overdue and send reminder
     */
    public function sendOverdueReminder(Invoice $invoice): ProcessRecord {
        
        return $this->executeAction(
            actionName: 'money.invoice.send_overdue_reminder',
            prerequisites: [
                fn() => $invoice->status !== 'paid',
                fn() => $invoice->getAttribute('due_date') < now(),
            ],
            execute: function() use ($invoice) {
                // Send reminder
                ChannelSentinel::sendMessage(
                    to: $invoice->customer->getAttribute('email'),
                    channel: 'email',
                    content: "Your invoice of \${$invoice->getAttribute('amount')} is now overdue."
                );
                
                return ['reminder_sent' => true];
            },
            signalName: 'money.invoice.overdue'
        );
    }
    
    /**
     * Forecast revenue
     */
    public function forecastRevenue(Company $company, int $months = 3): array {
        
        // Get all non-paid invoices
        $invoices = Entity::where('entity_type', 'invoice')
            ->where('company_id', $company->id)
            ->where('status', '!=', 'paid')
            ->get();
        
        $forecast = [];
        for ($m = 0; $m < $months; $m++) {
            $month = now()->addMonths($m);
            $forecast[$month->format('Y-m')] = 0;
            
            foreach ($invoices as $invoice) {
                $amount = $invoice->getAttribute('amount');
                $collected = $invoice->getAttribute('collected') ?? 0;
                $remaining = $amount - $collected;
                
                if ($remaining > 0 && $invoice->getAttribute('due_date') < $month->endOfMonth()) {
                    $forecast[$month->format('Y-m')] += $remaining;
                }
            }
        }
        
        return $forecast;
    }
}
```

---

## V. GROWTHSENTINEL & ADMINSENTINEL

Similar structure to above, following the same pattern:

```php
// GrowthSentinel
public function launchCampaign(Company $company, array $campaignData): ProcessRecord {
    // ... executeAction pattern
}

public function publishPost(Post $post): ProcessRecord {
    // ... executeAction pattern
}

public function suggestRebook(Job $completedJob): ProcessRecord {
    // ... executeAction pattern
}

// AdminSentinel
public function grantPermission(User $user, string $permission): ProcessRecord {
    // ... executeAction pattern
}

public function installExtension(Extension $extension): ProcessRecord {
    // ... executeAction pattern  (requires_approval = true)
}

public function updatePolicy(string $policyKey, $value): ProcessRecord {
    // ... executeAction pattern
}
```

---

## VI. INTEGRATION EXAMPLE

How a request flows through a Sentinel:

```php
// In controller (thin stub)
class JobController {
    public function __construct(private WorkSentinel $sentinel) {}
    
    public function store(StoreJobRequest $request) {
        $record = $this->sentinel->scheduleJob(
            company: Auth::user()->company,
            jobData: $request->validated()
        );
        
        // Return ProcessRecord as response
        return response()->json([
            'status' => $record->state,
            'process_record_id' => $record->id,
            'data' => $record->execution_result
        ]);
    }
}

// User submits form
// ↓
// Controller validates request
// ↓
// Calls WorkSentinel.scheduleJob()
// ↓
// Sentinel:
//   1. Creates ProcessRecord (track action)
//   2. Validates prerequisites
//   3. Checks permissions (AEGIS)
//   4. Executes domain logic
//   5. Emits signal
//   6. Returns ProcessRecord
// ↓
// Controller returns response
```

---

## CONCLUSION

Five Sentinels, 2,000 lines of code, eliminate:
- 150+ scattered controllers
- 300+ duplicate business logic
- 50+ permission checking scenarios
- 40+ different approval workflows
- Countless state machines

One unified pattern. Perfect scalability.

