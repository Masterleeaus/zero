# TitanOmni Security Hardening Guide

**Version:** Pass 26 + Hardening  
**Date:** March 30, 2026  
**Status:** Production-Ready

---

## Overview

This document outlines all security enhancements and hardening measures added to TitanOmni Pass 26, including:

- Exception handling with structured error responses
- Webhook signature verification (Twilio, Facebook, Telegram)
- Rate limiting on all endpoints
- Audit logging for compliance (PCI-DSS, GDPR, HIPAA)
- Credential encryption at rest
- Input validation and sanitization
- Comprehensive test coverage

---

## 1. Exception Handling

### Files Added

- `app/Exceptions/OmniException.php` — Base exception with logging
- `app/Exceptions/OmniChannelException.php` — Channel-specific errors with webhook context
- `app/Exceptions/OmniKnowledgeException.php` — Knowledge base operation errors
- `app/Exceptions/OmniVoiceException.php` — Voice/PSTN/TTS errors

### Features

**Structured Error Responses:**
```json
{
  "error": {
    "code": "CHANNEL_ERROR",
    "message": "Webhook signature verification failed",
    "status": 401,
    "timestamp": "2026-03-30T14:22:15Z",
    "context": {
      "driver": "twilio",
      "webhook_id": "msg_123abc",
      "retriable": true
    }
  }
}
```

**Automatic Logging:**
All exceptions log to `storage/logs/laravel.log` with:
- Full stack trace
- Request context (user_id, company_id)
- Sanitized payload (secrets removed)

**Usage in Services:**
```php
throw new OmniChannelException(
    'twilio',
    'Call initiation failed',
    503,
    'VOICE_CALL_INIT_FAILED',
    $webhookId,
    true, // retriable
    ['phone' => $number]
);
```

---

## 2. Webhook Security

### Middleware: VerifyWebhookSignature

**Location:** `app/Http/Middleware/VerifyWebhookSignature.php`

**Verification Schemes Implemented:**

#### Twilio (WhatsApp, SMS, Voice)
- Algorithm: HMAC-SHA1
- Validates `X-Twilio-Signature` header
- Reconstructs URL + sorted POST params
- Configuration: `config('services.twilio.auth_token')`

#### Facebook Messenger
- Algorithm: HMAC-SHA1 (`sha1=` prefix)
- Validates `X-Hub-Signature` header
- Configuration: `config('services.facebook.app_secret')`

#### Telegram
- Token-based verification via header or query
- Configuration: `config('services.telegram.bot_token')`

**Usage in Routes:**
```php
Route::post('/webhook/{driver}', WebhookController::class)
    ->middleware(VerifyWebhookSignature::class);
```

**Error Response (401):**
```json
{
  "error": {
    "code": "WEBHOOK_SIGNATURE_INVALID",
    "message": "Webhook signature verification failed",
    "driver": "twilio",
    "status": 401
  }
}
```

### Environment Setup

```bash
# .env
OMNI_WEBHOOK_VERIFICATION=true

# Services
TWILIO_AUTH_TOKEN=your_token_here
FACEBOOK_APP_SECRET=your_secret_here
TELEGRAM_BOT_TOKEN=your_token_here
```

---

## 3. Rate Limiting

### Middleware: RateLimitOmniEndpoints

**Location:** `app/Http/Middleware/RateLimitOmniEndpoints.php`

**Default Limits (per minute per user/company):**
- `conversation.store` — 100 requests/min
- `conversation.read` — 300 requests/min
- `voice.inbound` — 10 calls/min
- `voice.callback` — 5 calls/min
- `knowledge.search` — 200 requests/min

**Configuration:** `config/omni.php`
```php
'rate_limits' => [
    'conversation.store' => [
        'requests' => 100,
        'decay' => 1,  // minutes
    ],
],
```

**Override via Environment:**
```bash
OMNI_RATE_LIMIT_STORE=150     # Increase to 150/min
OMNI_RATE_LIMIT_VOICE_IN=20   # Increase to 20/min
```

**Rate Limit Exceeded Response (429):**
```json
{
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests. Please try again later.",
    "status": 429,
    "retry_after_seconds": 45,
    "limit": 100,
    "window_minutes": 1
  }
}
```

**Response Headers:**
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 2
X-RateLimit-Reset: 1711854600
Retry-After: 45
```

### Usage in Routes

```php
Route::post('/conversation', ConversationController::class)
    ->middleware([
        'auth',
        RateLimitOmniEndpoints::class
    ]);
```

---

## 4. Audit Logging (Compliance)

### Features

Comprehensive activity tracking for:
- **PCI-DSS:** Card data handling audits
- **GDPR:** Data access and modification logs
- **HIPAA:** Patient interaction audit trails

### Files Added

- `app/Traits/LogsActivity.php` — Model-level audit logging
- `database/migrations/2026_03_30_create_omni_audit_logs.php` — Audit table schema

### Schema: omni_audit_logs

```sql
CREATE TABLE omni_audit_logs (
  id BIGINT PRIMARY KEY,
  model_type VARCHAR(255),          -- App\Models\Omni\OmniMessage
  model_id BIGINT,                  -- Message ID
  table VARCHAR(100),               -- omni_messages
  action VARCHAR(50),               -- create, update, delete
  user_id BIGINT,                   -- Who performed action
  company_id BIGINT,                -- Tenant context
  old_values JSON,                  -- Previous state
  new_values JSON,                  -- New state
  ip_address VARCHAR(45),           -- IPv4/IPv6
  user_agent TEXT,                  -- Browser/API client
  created_at TIMESTAMP,
  
  INDEX(model_type, model_id, created_at),
  INDEX(company_id, created_at)
);
```

### Usage in Models

```php
class OmniMessage extends Model
{
    use LogsActivity;
    
    // Only audit these fields
    protected $auditLogFields = [
        'content',
        'role',
        'is_internal_note',
        'voice_transcript',
    ];
}
```

### Querying Audit Logs

```php
// Get all changes to a message
$audits = DB::table('omni_audit_logs')
    ->where('model_type', OmniMessage::class)
    ->where('model_id', $messageId)
    ->orderBy('created_at', 'desc')
    ->get();

// Example output:
// [
//   {
//     action: 'create',
//     user_id: 42,
//     new_values: {"content": "Hello", "role": "user"},
//     created_at: "2026-03-30T14:22:15Z"
//   }
// ]
```

### Enable/Disable

```bash
# .env
OMNI_AUDIT_LOGGING=true
OMNI_AUDIT_RETENTION=90  # Keep 90 days, then purge
```

### Retention Policy

Automated cleanup job (run daily):
```bash
php artisan omni:purge-audit-logs
```

---

## 5. Credential Encryption

### Migration: encrypt_bridge_secrets

**Location:** `database/migrations/2026_03_30_encrypt_bridge_secrets.php`

**What It Does:**
1. Adds `is_secret_encrypted` flag to omni_channel_bridges
2. Adds `secret_hash` field for validation without decryption
3. Encrypts all existing secrets using Laravel's encryption
4. Idempotent (safe to run multiple times)

**Before:**
```sql
SELECT bridge_secret FROM omni_channel_bridges;
-- Result: "sk_live_abc123def456"  (plaintext)
```

**After:**
```sql
SELECT bridge_secret, is_secret_encrypted, secret_hash FROM omni_channel_bridges;
-- Result: 
-- bridge_secret: "eyJpdiI6IjJuUlJY..." (encrypted)
-- is_secret_encrypted: true
-- secret_hash: "8a9e3c12..." (SHA256)
```

### Usage

In services, decrypt on-the-fly:
```php
$bridge = OmniChannelBridge::find($id);

// Automatically decrypts
$secret = $bridge->bridge_secret;

// Laravel casts automatically decrypt
$decrypted = decrypt($bridge->bridge_secret);
```

### Key Rotation

When Laravel's APP_KEY rotates, use command:
```bash
php artisan omni:rotate-encryption
```

---

## 6. Input Validation & Sanitization

### Enhanced OmniConversationService

**Location:** `app/Services/Omni/OmniConversationService.php`

**Validations:**

1. **company_id** — Required, numeric
2. **agent_id** — Required, numeric
3. **customer_email** — Optional, valid email format
4. **message** — Content, voice_file_url, or media_url required
5. **role** — Enum: user | assistant | system
6. **message_type** — Enum: text | voice_transcript | media | etc.
7. **voice_confidence** — Clamped to [0, 1]
8. **media_size_bytes** — Enforces max size limit

**Error Examples:**

```php
// Invalid email
throw new OmniException(
    'Invalid customer email format',
    0, null, 400,
    'INVALID_EMAIL',
    ['email' => 'not-an-email']
);

// Media too large
throw new OmniException(
    'Media size exceeds limit: 150MB > 100MB',
    0, null, 413,
    'MEDIA_SIZE_EXCEEDED',
    ['provided' => 150000000, 'max' => 100000000]
);
```

### Sanitization

Sensitive fields removed from logs:
```php
protected function sanitizeAttributes(array $attributes): array
{
    unset(
        $attributes['bridge_secret'],
        $attributes['api_key'],
        $attributes['token']
    );
    return $attributes;
}
```

---

## 7. Test Coverage

### Test Files Added

| File | Tests | Coverage |
|------|-------|----------|
| `tests/Feature/Omni/OmniConversationTest.php` | 10 | Feature-level |
| `tests/Unit/Omni/OmniConversationServiceTest.php` | 11 | Service logic |
| `tests/Unit/Http/Middleware/VerifyWebhookSignatureTest.php` | 6 | Webhook verification |

### Running Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/Omni/OmniConversationTest.php

# With code coverage
php artisan test --coverage

# Watch mode (re-run on file change)
php artisan test --watch
```

### Example Test

```php
public function test_can_create_conversation(): void
{
    $response = $this->postJson('/dashboard/user/omni/conversation', [
        'company_id' => 1,
        'agent_id' => 100,
        'message' => 'Hello',
    ])->assertStatus(200)
     ->assertJsonStructure(['conversation_id', 'reply', 'mode']);

    $this->assertDatabaseHas('omni_conversations', [
        'company_id' => 1,
        'agent_id' => 100,
    ]);
}
```

---

## 8. Deployment Checklist

### Pre-Production

- [ ] **Database Migrations**
  ```bash
  php artisan migrate --path=database/migrations
  ```
  Runs:
  - `2026_03_30_create_omni_audit_logs.php` (adds audit table)
  - `2026_03_30_encrypt_bridge_secrets.php` (encrypts credentials)

- [ ] **Environment Variables**
  ```bash
  # .env
  OMNI_WEBHOOK_VERIFICATION=true
  OMNI_AUDIT_LOGGING=true
  OMNI_ENCRYPT_CREDENTIALS=true
  TWILIO_AUTH_TOKEN=***
  FACEBOOK_APP_SECRET=***
  ELEVENLABS_API_KEY=***
  ```

- [ ] **Middleware Registration** (in `app/Http/Kernel.php`)
  ```php
  protected $routeMiddleware = [
      // ...
      'omni.webhook' => \App\Http\Middleware\VerifyWebhookSignature::class,
      'omni.ratelimit' => \App\Http\Middleware\RateLimitOmniEndpoints::class,
  ];
  ```

- [ ] **Route Protection**
  ```php
  Route::post('/webhook/{driver}', WebhookController::class)
      ->middleware(['omni.webhook']);
      
  Route::post('/conversation', ConversationController::class)
      ->middleware(['auth', 'omni.ratelimit']);
  ```

- [ ] **Configuration Publishing**
  ```bash
  php artisan vendor:publish --provider="TitanOmniServiceProvider" --tag="config"
  ```

- [ ] **Audit Log Retention Scheduled**
  ```php
  // app/Console/Kernel.php
  $schedule->command('omni:purge-audit-logs')->daily();
  ```

- [ ] **Test Suite Passing**
  ```bash
  php artisan test --coverage
  ```

### Post-Production

- [ ] **Monitor Error Logs**
  ```bash
  tail -f storage/logs/laravel.log | grep "Omni"
  ```

- [ ] **Check Rate Limit Headers**
  ```bash
  curl -i http://api.titanzero.io/dashboard/user/omni/conversations
  # Verify X-RateLimit-* headers present
  ```

- [ ] **Verify Audit Logging**
  ```sql
  SELECT COUNT(*) FROM omni_audit_logs WHERE DATE(created_at) = CURDATE();
  ```

- [ ] **Test Webhook Signatures**
  ```bash
  # Send test Twilio webhook with invalid signature
  curl -X POST /webhook/twilio \
    -H "X-Twilio-Signature: invalid" \
    -d "From=+1234567890&Body=test"
  # Should return 401
  ```

---

## 9. Monitoring & Alerting

### Key Metrics to Monitor

```yaml
Errors:
  - omni_exception_rate (errors per minute)
  - webhook_signature_failures (failed verifications)
  - rate_limit_exceeded (HTTP 429 responses)

Performance:
  - conversation_create_latency_ms (p50, p99)
  - webhook_processing_time_ms
  - knowledge_search_latency_ms

Business:
  - conversations_per_minute (throughput)
  - messages_per_conversation (engagement)
  - channel_distribution (which channels most used)
```

### Alert Rules

```bash
# Alert if signature verification failure rate > 5%
alert_if_webhook_signature_failure_rate > 0.05

# Alert if response time > 2s
alert_if_response_time_p99_ms > 2000

# Alert if rate limit exceeded > 100/hour
alert_if_rate_limited_requests > 100
```

---

## 10. Security Best Practices

### API Key Management

✅ **DO:**
- Store API keys in `config/services.php` (loaded from `.env`)
- Rotate keys quarterly
- Use webhook signature verification (not API key in body)
- Log key usage events

❌ **DON'T:**
- Commit `.env` files to Git
- Log API keys or secrets
- Send keys in query parameters
- Hardcode credentials

### Database Security

✅ **DO:**
- Encrypt bridge credentials (automated by migration)
- Use row-level security via company_id partitioning
- Enable binary logging for audit trail
- Use read replicas for reporting

❌ **DON'T:**
- Store unencrypted secrets in DB
- Query across company_id boundaries
- Expose database connection string in logs

### Network Security

✅ **DO:**
- Use HTTPS everywhere (enforce via middleware)
- Validate webhook signatures
- Rate limit endpoints
- Use VPC/private networking for services

❌ **DON'T:**
- Accept HTTP webhook payloads
- Trust headers (X-Forwarded-For) without verification
- Expose internal service URLs

---

## 11. Troubleshooting

### Webhook Signature Verification Fails

```
Error: Webhook signature verification failed

Causes:
1. Token/secret configured incorrectly in .env
2. Signature scheme not supported for driver
3. Request body modified in transit

Solution:
- Verify TWILIO_AUTH_TOKEN in .env
- Check driver name in route ({driver} param)
- Log raw request in middleware for debugging
```

### Rate Limit Too Strict

```
Error: {"error": {"code": "RATE_LIMIT_EXCEEDED"}}

Solution:
Adjust in .env:
  OMNI_RATE_LIMIT_STORE=200    # Increase to 200/min
```

### Audit Logs Growing Too Fast

```
Error: omni_audit_logs table consuming disk space

Solution:
1. Reduce OMNI_AUDIT_RETENTION=30 (30 days instead of 90)
2. Run: php artisan omni:purge-audit-logs
3. Add index on (company_id, created_at) for faster deletes
```

---

## 12. Summary

| Category | Coverage | Status |
|----------|----------|--------|
| **Exception Handling** | 4 custom exception classes | ✅ Complete |
| **Webhook Security** | Twilio, Facebook, Telegram HMAC verification | ✅ Complete |
| **Rate Limiting** | 5 configurable endpoint limits | ✅ Complete |
| **Audit Logging** | Comprehensive model-level tracking | ✅ Complete |
| **Encryption** | At-rest credential encryption | ✅ Complete |
| **Input Validation** | Email, role, type, size checks | ✅ Complete |
| **Test Coverage** | 27+ unit & feature tests | ✅ Complete |
| **Documentation** | This guide + inline comments | ✅ Complete |

**Production-Ready:** Yes ✓

---

**Next Steps:**
1. Deploy migrations to production
2. Configure environment variables
3. Run test suite to verify
4. Monitor logs and metrics
5. Set up alerting
6. Document any custom modifications

