# TITANZEROVNEXUS: API & SDK Reference

**Version:** 5.0  
**Document:** 009  
**Status:** Complete API Specification

---

## EXECUTIVE SUMMARY

TitanZero Nexus exposes a unified REST API + Laravel SDK that abstracts complexity. Developers don't need to understand five Sentinels; they just call `Nexus::action()` and it routes correctly.

---

## I. REST API SPECIFICATION

### **Base URL**
```
https://api.titanzero.io/v1
```

### **Authentication**
```
Authorization: Bearer {api_token}
X-Tenant-ID: {tenant_id}
```

---

## II. UNIVERSAL ACTION ENDPOINT

Every action flows through one endpoint:

```
POST /actions/{mode}/{action}

Body:
{
  "intent": "Schedule a cleaning job for tomorrow",
  "data": {
    "customer_id": 123,
    "location_id": 456,
    "scheduled_at": "2026-04-05 10:00:00",
    "checklist_template": "standard_office"
  },
  "options": {
    "notify_customer": true,
    "require_photo_evidence": true,
    "auto_invoice": true
  }
}

Response:
{
  "status": "pending_approval",
  "process_record_id": "pr_xyz789",
  "process_state": "processing",
  "requires_approval": true,
  "approval_deadline": "2026-04-04 17:00:00",
  "data": {
    "job_id": "job_123",
    "job_number": "JOB-2026-001234"
  },
  "links": {
    "approve": "/approvals/pr_xyz789/approve",
    "reject": "/approvals/pr_xyz789/reject",
    "details": "/process-records/pr_xyz789"
  }
}
```

---

## III. EXAMPLE: WORK MODE ACTIONS

### **Schedule a Job**

```bash
curl -X POST https://api.titanzero.io/v1/actions/work/schedule_job \
  -H "Authorization: Bearer api_sk_..." \
  -H "X-Tenant-ID: tenant_1" \
  -H "Content-Type: application/json" \
  -d '{
    "intent": "Schedule cleaning job",
    "data": {
      "title": "Office building deep clean",
      "company_id": 5,
      "location_id": 123,
      "scheduled_at": "2026-04-05 10:00:00",
      "estimated_duration_minutes": 180,
      "checklist_template": "office_deep_clean",
      "customer_notes": "Access code: 1234"
    },
    "options": {
      "notify_customer": true,
      "send_technician_reminder": true,
      "require_before_after_photos": true
    }
  }'

Response (201 Created):
{
  "status": "created",
  "process_record_id": "pr_abc123",
  "job_id": "job_456",
  "job_number": "JOB-2026-005678",
  "scheduled_at": "2026-04-05T10:00:00Z",
  "status": "proposed",
  "links": {
    "self": "/jobs/job_456",
    "technician_assignment": "/jobs/job_456/assign_technician"
  }
}
```

### **Start a Job**

```bash
curl -X POST https://api.titanzero.io/v1/actions/work/start_job \
  -H "Authorization: Bearer api_sk_..." \
  -H "X-Tenant-ID: tenant_1" \
  -d '{
    "intent": "Begin work on job",
    "data": {
      "job_id": "job_456",
      "started_by_user_id": 789,
      "technician_arrival_time": "2026-04-05T09:45:00Z"
    }
  }'

Response (200 OK):
{
  "status": "executed",
  "job_id": "job_456",
  "job_status": "processing",
  "started_at": "2026-04-05T09:45:00Z",
  "checklist": {
    "items": [
      {"id": "item_1", "name": "Vacuum floors", "completed": false},
      {"id": "item_2", "name": "Clean restrooms", "completed": false}
    ],
    "progress_percent": 0
  }
}
```

### **Complete a Job**

```bash
curl -X POST https://api.titanzero.io/v1/actions/work/complete_job \
  -H "Authorization: Bearer api_sk_..." \
  -H "X-Tenant-ID: tenant_1" \
  -d '{
    "intent": "Mark job as finished",
    "data": {
      "job_id": "job_456",
      "completed_by_user_id": 789,
      "notes": "Building clean, no issues encountered",
      "evidence": {
        "photos": [
          {
            "file_path": "s3://photos/job_456/before_1.jpg",
            "caption": "Entry lobby before"
          },
          {
            "file_path": "s3://photos/job_456/after_1.jpg",
            "caption": "Entry lobby after"
          }
        ]
      },
      "checklist_completion": {
        "item_1": true,
        "item_2": true
      }
    },
    "options": {
      "auto_create_invoice": true,
      "send_completion_email": true
    }
  }'

Response (200 OK):
{
  "status": "executed",
  "job_id": "job_456",
  "job_status": "executed",
  "completed_at": "2026-04-05T13:30:00Z",
  "evidence_count": 2,
  "invoice_created": {
    "invoice_id": "inv_789",
    "amount": 450.00,
    "status": "sent"
  },
  "signals_emitted": [
    "work.job.completed",
    "money.invoice.created",
    "channel.message.sent"
  ]
}
```

---

## IV. EXAMPLE: MONEY MODE ACTIONS

### **Create Invoice**

```bash
curl -X POST https://api.titanzero.io/v1/actions/money/create_invoice \
  -H "Authorization: Bearer api_sk_..." \
  -d '{
    "intent": "Create invoice for job",
    "data": {
      "job_id": "job_456",
      "customer_id": 10,
      "company_id": 5,
      "line_items": [
        {
          "description": "Office deep clean",
          "quantity": 1,
          "unit_price": 450.00,
          "tax_rate": 0.10
        }
      ],
      "due_date": "2026-04-20",
      "payment_terms": "net_15",
      "notes": "Thank you for your business"
    },
    "options": {
      "send_immediately": true,
      "schedule_overdue_reminder": true
    }
  }'

Response (201 Created):
{
  "status": "created",
  "invoice_id": "inv_789",
  "invoice_number": "INV-2026-001234",
  "amount": 495.00,
  "status": "sent",
  "due_date": "2026-04-20",
  "sent_at": "2026-04-05T13:35:00Z",
  "links": {
    "view": "/invoices/inv_789",
    "payment_link": "https://pay.titanzero.io/inv_789"
  }
}
```

### **Record Payment**

```bash
curl -X POST https://api.titanzero.io/v1/actions/money/record_payment \
  -H "Authorization: Bearer api_sk_..." \
  -d '{
    "intent": "Log payment received",
    "data": {
      "invoice_id": "inv_789",
      "amount_received": 495.00,
      "payment_method": "card",
      "transaction_id": "txn_stripe_xyz",
      "received_at": "2026-04-10T14:22:00Z"
    }
  }'

Response (200 OK):
{
  "status": "executed",
  "invoice_id": "inv_789",
  "invoice_status": "paid",
  "payment_received": 495.00,
  "recorded_at": "2026-04-10T14:22:00Z",
  "signals_emitted": ["money.payment.received", "money.invoice.paid"]
}
```

---

## V. EXAMPLE: GROWTH MODE ACTIONS

### **Launch Campaign**

```bash
curl -X POST https://api.titanzero.io/v1/actions/growth/launch_campaign \
  -H "Authorization: Bearer api_sk_..." \
  -d '{
    "intent": "Start rebooking campaign",
    "data": {
      "campaign_name": "Spring Rebook Blitz",
      "campaign_type": "rebooking",
      "target_customers": {
        "last_job_before": "2026-01-01",
        "exclude_booked_within_90_days": true,
        "min_lifetime_value": 500
      },
      "offer": {
        "discount_percent": 15,
        "valid_until": "2026-05-31",
        "description": "Spring special: 15% off your next cleaning"
      },
      "channels": ["email", "sms", "whatsapp"],
      "schedule": {
        "start_at": "2026-04-06T08:00:00Z",
        "send_over_days": 7,
        "follow_up_after_days": [3, 7]
      }
    },
    "options": {
      "require_approval": true,
      "preview_before_send": true
    }
  }'

Response (201 Created):
{
  "status": "pending_approval",
  "campaign_id": "camp_abc123",
  "campaign_status": "draft",
  "estimated_reach": 1250,
  "estimated_cost": 45.00,
  "approval_deadline": "2026-04-05T17:00:00Z",
  "links": {
    "preview": "/campaigns/camp_abc123/preview",
    "approve": "/actions/campaigns/camp_abc123/approve"
  }
}
```

### **Suggest Rebook**

```bash
curl -X POST https://api.titanzero.io/v1/actions/growth/suggest_rebook \
  -H "Authorization: Bearer api_sk_..." \
  -d '{
    "intent": "Offer customer a rebook",
    "data": {
      "job_id": "job_456",
      "customer_id": 10,
      "suggested_service": "window_cleaning",
      "discount_offer": 0.10,
      "send_via": ["email", "sms"]
    }
  }'

Response (200 OK):
{
  "status": "executed",
  "suggestion_id": "sug_xyz789",
  "customer_id": 10,
  "suggested_service": "window_cleaning",
  "offer_discount": "10%",
  "messages_sent": [
    {"channel": "email", "status": "sent", "sent_at": "2026-04-05T13:40:00Z"},
    {"channel": "sms", "status": "sent", "sent_at": "2026-04-05T13:40:05Z"}
  ]
}
```

---

## VI. EXAMPLE: CHANNEL MODE ACTIONS

### **Send Message**

```bash
curl -X POST https://api.titanzero.io/v1/actions/channel/send_message \
  -H "Authorization: Bearer api_sk_..." \
  -d '{
    "intent": "Send job reminder",
    "data": {
      "to": "+1-555-123-4567",
      "channel": "sms",
      "message": "Reminder: Your cleaning is scheduled for tomorrow at 10 AM. Reply CONFIRM to confirm.",
      "customer_id": 10
    },
    "options": {
      "track_delivery": true,
      "wait_for_reply": false
    }
  }'

Response (200 OK):
{
  "status": "executed",
  "message_id": "msg_456",
  "channel": "sms",
  "status": "sent",
  "sent_at": "2026-04-04T18:30:00Z",
  "tracking_id": "twilio_xyz789"
}
```

### **Receive & Process Message**

```bash
curl -X POST https://api.titanzero.io/v1/actions/channel/receive_message \
  -H "Authorization: Bearer api_sk_..." \
  -d '{
    "intent": "Process incoming customer message",
    "data": {
      "from": "+1-555-123-4567",
      "channel": "whatsapp",
      "content": "Hi, can you reschedule my cleaning from Thursday to Friday?",
      "external_message_id": "wh_xyz789",
      "received_at": "2026-04-04T19:15:00Z"
    }
  }'

Response (200 OK):
{
  "status": "received",
  "message_id": "msg_457",
  "conversation_id": "conv_123",
  "intent_detected": "reschedule_job",
  "confidence": 0.98,
  "action_suggested": {
    "action": "work.reschedule_job",
    "parameters": {
      "job_id": "job_456",
      "new_scheduled_at": "2026-04-05T10:00:00Z"
    }
  },
  "requires_human_review": false
}
```

---

## VII. EXAMPLE: ADMIN MODE ACTIONS

### **Grant Permission**

```bash
curl -X POST https://api.titanzero.io/v1/actions/admin/grant_permission \
  -H "Authorization: Bearer api_sk_..." \
  -d '{
    "intent": "Add dispatcher to system",
    "data": {
      "user_id": 999,
      "mode": "work",
      "role": "dispatcher",
      "permissions": [
        "create_job",
        "assign_technician",
        "reschedule_job",
        "view_schedule"
      ]
    },
    "options": {
      "require_approval": true,
      "effective_date": "2026-04-05"
    }
  }'

Response (201 Created):
{
  "status": "pending_approval",
  "permission_id": "perm_xyz123",
  "user_id": 999,
  "mode": "work",
  "role": "dispatcher",
  "status": "pending",
  "approval_deadline": "2026-04-05T17:00:00Z"
}
```

---

## VIII. LARAVEL SDK

### **Installation**

```bash
composer require titanzero/nexus-sdk
php artisan nexus:install
```

### **Basic Usage**

```php
use TitanZero\Nexus\Facades\Nexus;

// Execute any action
$result = Nexus::action('work', 'schedule_job', [
    'intent' => 'Schedule cleaning',
    'data' => [
        'title' => 'Office clean',
        'company_id' => 5,
        'location_id' => 123,
        'scheduled_at' => now()->addDay(),
    ]
]);

// Check status
if ($result->isPending()) {
    // Waiting for approval
    return redirect("/approve/{$result->processRecordId}");
}

if ($result->isExecuted()) {
    // Successfully created
    $jobId = $result->data('job_id');
    return redirect("/jobs/$jobId");
}

if ($result->isRejected()) {
    // Failed validation
    return back()->withErrors($result->errors());
}
```

### **Query Entities**

```php
use TitanZero\Nexus\Models\Entity;

// Get all jobs for a company
$jobs = Entity::where('entity_type', 'job')
    ->where('company_id', 5)
    ->where('status', 'processing')
    ->orderBy('scheduled_at')
    ->get();

// Get with relationships
$job = Entity::with('attributes', 'relationships')
    ->where('id', $jobId)
    ->first();

// Access attributes
echo $job->getAttribute('title');
echo $job->getAttribute('scheduled_at');

// Access relationships
$technician = $job->relationshipTo('user', 'assigned_to')->first();
```

### **Listen to Signals**

```php
use TitanZero\Nexus\Events\Signal;
use Illuminate\Support\Facades\Event;

Event::listen(Signal::class, function (Signal $signal) {
    if ($signal->name === 'work.job.completed') {
        // Job completed, auto-invoice
        Nexus::action('money', 'create_invoice', [
            'data' => [
                'job_id' => $signal->entity_id,
                'auto_create' => true
            ]
        ]);
    }
});
```

### **Custom Sentinels**

```php
use TitanZero\Nexus\Sentinels\BaseSentinel;

class CustomSentinel extends BaseSentinel {
    protected string $mode = 'custom';
    
    public function doSomething($data) {
        return $this->executeAction(
            actionName: 'custom.action',
            prerequisites: [
                fn() => Auth::user()->can('do_something'),
            ],
            execute: fn() => [
                'result' => 'success',
                'data' => $data
            ],
            signalName: 'custom.action.executed'
        );
    }
}

// Register in config
// config/nexus.php:
// 'custom_sentinels' => [CustomSentinel::class]

// Use it
$result = Nexus::via(CustomSentinel::class)->doSomething($data);
```

---

## IX. ERROR HANDLING

### **API Error Response**

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Scheduled time must be in future",
    "details": {
      "field": "scheduled_at",
      "value": "2026-04-01T10:00:00Z",
      "constraint": "must_be_future"
    },
    "request_id": "req_xyz789"
  }
}
```

### **SDK Error Handling**

```php
try {
    $result = Nexus::action('work', 'schedule_job', $data);
} catch (ValidationException $e) {
    return back()->withErrors($e->errors());
} catch (PermissionDeniedException $e) {
    return abort(403, "You don't have permission to schedule jobs");
} catch (ProcessRecordException $e) {
    return back()->withError("Action failed: " . $e->getMessage());
}
```

---

## X. RATE LIMITING & QUOTAS

```
Rate Limits:

Free Tier:
├─ 100 API calls/day
├─ 50 messages/day
└─ 10 actions/minute

Growth Tier:
├─ 10,000 API calls/day
├─ 5,000 messages/day
└─ 100 actions/minute

Enterprise Tier:
├─ Unlimited
├─ Custom quotas
└─ Dedicated support
```

### **Checking Quota**

```php
$quota = Nexus::quota('work_actions');
echo $quota->remaining; // 87 remaining
echo $quota->limit; // 100 total
echo $quota->resets_at; // DateTime

if ($quota->remaining < 10) {
    // Near limit, cache or batch requests
}
```

---

## XI. WEBHOOKS

### **Register Webhook**

```bash
curl -X POST https://api.titanzero.io/v1/webhooks \
  -H "Authorization: Bearer api_sk_..." \
  -d '{
    "url": "https://your-app.com/webhooks/nexus",
    "events": [
      "work.job.completed",
      "money.payment.received",
      "growth.lead.converted"
    ],
    "secret": "whsec_xxx" // For HMAC verification
  }'
```

### **Receive Webhook**

```php
Route::post('/webhooks/nexus', function (Request $request) {
    // Verify signature
    $signature = $request->header('X-Nexus-Signature');
    $payload = $request->getContent();
    $expected = hash_hmac('sha256', $payload, config('nexus.webhook_secret'));
    
    if (!hash_equals($expected, $signature)) {
        return abort(401, 'Invalid signature');
    }
    
    $event = $request->json();
    
    // Handle event
    match($event['event']) {
        'work.job.completed' => handleJobCompleted($event),
        'money.payment.received' => handlePaymentReceived($event),
        default => null
    };
    
    return response()->json(['ok' => true]);
});
```

---

## CONCLUSION

The Nexus API is simple: one endpoint, five modes, infinite actions.

Developers don't think about Sentinels or state machines. They think about intent.

```
POST /actions/{mode}/{action}
Body: {intent, data, options}
Response: {status, data, links}
```

That's the entire API surface. Everything else flows from that.

