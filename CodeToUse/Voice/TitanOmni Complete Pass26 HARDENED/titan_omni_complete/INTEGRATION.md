# TitanOmni Hardening Integration Guide

**Target:** TitanOmni Pass 26 (System-Only)  
**Date:** March 30, 2026  
**Effort:** 2-3 hours to integrate + 1 hour testing

---

## Overview

This hardening package adds production-grade security, error handling, logging, and testing to TitanOmni. It's designed for seamless integration with the existing Pass 26 codebase.

### What's Included

```
TitanOmni_Hardened/
├── app/
│   ├── Exceptions/                    [NEW] Custom exception classes
│   │   ├── OmniException.php
│   │   ├── OmniChannelException.php
│   │   ├── OmniKnowledgeException.php
│   │   └── OmniVoiceException.php
│   ├── Http/
│   │   └── Middleware/               [NEW] Security middleware
│   │       ├── VerifyWebhookSignature.php
│   │       └── RateLimitOmniEndpoints.php
│   ├── Traits/                       [NEW] Reusable functionality
│   │   └── LogsActivity.php          (Audit logging)
│   └── Services/Omni/
│       └── OmniConversationService.php [ENHANCED] Error handling, validation
├── config/
│   └── omni.php                      [ENHANCED] Security, rate limits, audit
├── database/
│   └── migrations/
│       ├── 2026_03_30_create_omni_audit_logs.php [NEW]
│       └── 2026_03_30_encrypt_bridge_secrets.php [NEW]
├── tests/
│   ├── Feature/
│   │   └── Omni/
│   │       └── OmniConversationTest.php [NEW]
│   └── Unit/
│       ├── Omni/
│       │   └── OmniConversationServiceTest.php [NEW]
│       └── Http/Middleware/
│           └── VerifyWebhookSignatureTest.php [NEW]
├── SECURITY_HARDENING.md             [NEW] Comprehensive guide
└── INTEGRATION.md                    [NEW] This file
```

---

## Step-by-Step Integration

### Step 1: Backup Existing Code

```bash
cp -r /path/to/TitanZero/app app.backup
cp -r /path/to/TitanZero/config/omni.php omni.php.backup
cp -r /path/to/TitanZero/database/migrations migrations.backup
```

### Step 2: Copy Exception Classes

```bash
# Copy exception files
cp app/Exceptions/*.php /path/to/TitanZero/app/Exceptions/

# Verify
ls -la /path/to/TitanZero/app/Exceptions/
# Should list: OmniException, OmniChannelException, etc.
```

### Step 3: Copy Middleware

```bash
# Copy middleware files
cp app/Http/Middleware/*.php /path/to/TitanZero/app/Http/Middleware/

# Register in app/Http/Kernel.php
# Add to $routeMiddleware:
# 'omni.webhook' => \App\Http\Middleware\VerifyWebhookSignature::class,
# 'omni.ratelimit' => \App\Http\Middleware\RateLimitOmniEndpoints::class,
```

**Edit `app/Http/Kernel.php`:**
```php
protected $routeMiddleware = [
    // ... existing middleware ...
    'omni.webhook' => \App\Http\Middleware\VerifyWebhookSignature::class,
    'omni.ratelimit' => \App\Http\Middleware\RateLimitOmniEndpoints::class,
];
```

### Step 4: Copy Traits

```bash
cp app/Traits/*.php /path/to/TitanZero/app/Traits/
```

### Step 5: Replace OmniConversationService

```bash
# Backup original
cp /path/to/TitanZero/app/Services/Omni/OmniConversationService.php \
   /path/to/TitanZero/app/Services/Omni/OmniConversationService.php.backup

# Copy hardened version
cp app/Services/Omni/OmniConversationService.php \
   /path/to/TitanZero/app/Services/Omni/
```

### Step 6: Update Configuration

```bash
# Backup original
cp /path/to/TitanZero/config/omni.php /path/to/TitanZero/config/omni.php.backup

# Copy enhanced config
cp config/omni.php /path/to/TitanZero/config/
```

**Then update `.env`:**
```bash
# Security
OMNI_WEBHOOK_VERIFICATION=true
OMNI_ENCRYPT_CREDENTIALS=true
OMNI_AUDIT_LOGGING=true

# Rate limits
OMNI_RATE_LIMIT_STORE=100
OMNI_RATE_LIMIT_READ=300
OMNI_RATE_LIMIT_VOICE_IN=10

# Audit retention
OMNI_AUDIT_RETENTION=90

# External services
TWILIO_AUTH_TOKEN=your_token_here
FACEBOOK_APP_SECRET=your_secret_here
ELEVENLABS_API_KEY=your_key_here
```

### Step 7: Add Migrations

```bash
# Copy migration files
cp database/migrations/2026_03_30_*.php \
   /path/to/TitanZero/database/migrations/

# Run migrations
php artisan migrate --path=database/migrations
```

This creates:
- `omni_audit_logs` table (for compliance tracking)
- Encrypts existing `omni_channel_bridges.bridge_secret` values

### Step 8: Add Tests

```bash
# Copy test files
cp -r tests/* /path/to/TitanZero/tests/

# Run tests to verify
php artisan test
```

### Step 9: Update Routes (Webhook Protection)

In `routes/panel.php` or your webhook route file:

**Before:**
```php
Route::post('/webhook/{driver}', WebhookController::class);
```

**After:**
```php
Route::post('/webhook/{driver}', WebhookController::class)
    ->middleware(\App\Http\Middleware\VerifyWebhookSignature::class);
```

### Step 10: Update Conversation Routes (Rate Limiting)

In `routes/panel.php`:

**Before:**
```php
Route::post('/conversation', ConversationController::class);
```

**After:**
```php
Route::post('/conversation', ConversationController::class)
    ->middleware(\App\Http\Middleware\RateLimitOmniEndpoints::class);
```

---

## Verification Checklist

After integration, verify everything works:

### 1. Migrations Applied

```bash
php artisan migrate:status

# Should show:
# 2026_03_30_create_omni_audit_logs    [OK]
# 2026_03_30_encrypt_bridge_secrets    [OK]
```

### 2. Tables Exist

```bash
php artisan tinker
>>> DB::table('omni_audit_logs')->count()
=> 0

>>> DB::table('omni_channel_bridges')
    ->where('is_secret_encrypted', true)
    ->count()
=> 5  (or however many bridges you have)
```

### 3. Exceptions Work

```bash
php artisan tinker

>>> throw new \App\Exceptions\OmniException(
  'Test error',
  0, null, 500,
  'TEST_ERROR'
);

# Should see structured exception in logs
```

### 4. Middleware Loaded

```bash
php artisan route:list | grep omni

# Should show routes with omni.webhook and omni.ratelimit middleware
```

### 5. Tests Pass

```bash
php artisan test

# Expected output:
# Tests:  27 passed (48 assertions)
# Time:   2.345s
```

### 6. Configuration Published

```php
// In artisan tinker:
>>> config('omni.enable_webhook_verification')
=> true

>>> config('omni.rate_limits.conversation.store')
=> ['requests' => 100, 'decay' => 1]
```

---

## Rollback Plan

If issues occur, rollback is simple:

```bash
# Restore services and config
cp app.backup/Services/Omni/OmniConversationService.php \
   /path/to/TitanZero/app/Services/Omni/
cp omni.php.backup /path/to/TitanZero/config/omni.php

# Rollback new migrations
php artisan migrate:rollback --step=2

# Restore middleware from backup (or remove from routes)
```

---

## Testing the Hardening

### Test 1: Webhook Signature Verification

```bash
# Invalid signature should return 401
curl -X POST http://localhost:8000/webhook/twilio \
  -H "X-Twilio-Signature: invalid_signature" \
  -d "From=+1234567890&Body=test"

# Expected: 401 Unauthorized with error code WEBHOOK_SIGNATURE_INVALID
```

### Test 2: Rate Limiting

```bash
# Run 101+ requests to conversation endpoint
for i in {1..102}; do
  curl -X POST http://localhost:8000/dashboard/user/omni/conversation \
    -H "Content-Type: application/json" \
    -d '{"company_id":1,"agent_id":100,"message":"test"}'
done

# Request #101+ should return 429 Too Many Requests
```

### Test 3: Exception Handling

```bash
# Invalid email format
curl -X POST http://localhost:8000/dashboard/user/omni/conversation \
  -H "Content-Type: application/json" \
  -d '{
    "company_id":1,
    "agent_id":100,
    "customer_email":"not-an-email",
    "message":"test"
  }'

# Expected: 400 Bad Request with error code INVALID_EMAIL
```

### Test 4: Audit Logging

```bash
php artisan tinker

# Create a conversation to trigger audit log
>>> use App\Services\Omni\OmniConversationService;
>>> $service = app(OmniConversationService::class);
>>> $conv = $service->findOrCreate([
  'company_id' => 1,
  'agent_id' => 100,
  'message' => 'test'
]);

# Check audit log was created
>>> DB::table('omni_audit_logs')
  ->where('action', 'create')
  ->count()
=> 1  (or more if other creates happened)
```

### Test 5: Credential Encryption

```bash
php artisan tinker

# Check encrypted secrets
>>> DB::table('omni_channel_bridges')
  ->select('id', 'is_secret_encrypted', 'bridge_secret')
  ->first();

# bridge_secret should be encrypted (starts with eyJ...)
# is_secret_encrypted should be true
```

---

## Troubleshooting Integration

### Issue: Route Middleware Not Found

```
Error: Class 'App\Http\Middleware\VerifyWebhookSignature' not found
```

**Solution:**
- Verify file copied to `app/Http/Middleware/`
- Run `composer dump-autoload`
- Verify kernel.php has correct namespace

### Issue: Migration Fails with "Table Already Exists"

```
Error: SQLSTATE[HY000]: General error: 1030 Table or disk full (in omni_audit_logs)
```

**Solution:**
```bash
# Check if table exists
php artisan tinker
>>> Schema::hasTable('omni_audit_logs')
=> true

# If true, rollback and comment out migration
php artisan migrate:rollback --step=1
# Edit migration: comment out create_omni_audit_logs
php artisan migrate
```

### Issue: Rate Limit Middleware Not Working

```
# All requests succeed even after rate limit exceeded
```

**Solution:**
- Verify middleware added to route: `->middleware('omni.ratelimit')`
- Verify kernel.php has correct binding
- Check if cache is configured: `php artisan config:cache`

### Issue: Webhook Verification Fails in Production

```
Error: Webhook signature verification failed
```

**Solution:**
- Verify `TWILIO_AUTH_TOKEN` is correct in production `.env`
- Check if request is being modified by proxy (logging middleware, etc.)
- Temporarily disable verification to test: `OMNI_WEBHOOK_VERIFICATION=false`

---

## Performance Considerations

### Before Integration
- No cryptographic operations on every request
- Simple validation

### After Integration
- Webhook signature verification: **~1-2ms** per request (HMAC-SHA1/256)
- Rate limit checking: **~0.5ms** per request (Redis cache hit)
- Audit logging: **~0.1ms** per request (JSON serialization)
- **Total overhead: ~2ms per request** ✓ (acceptable)

### Optimization Tips

1. **Cache webhook configuration:**
   ```php
   config(['services.twilio.auth_token' => cache('twilio_token')])
   ```

2. **Batch audit log inserts:**
   ```php
   DB::table('omni_audit_logs')->insert($logs);  // Insert multiple
   ```

3. **Use read replicas for audit queries:**
   ```php
   DB::connection('read')->table('omni_audit_logs')->get();
   ```

---

## Maintaining Hardening

### Weekly
- [ ] Review error logs for patterns: `grep "OmniException" storage/logs/laravel.log`
- [ ] Monitor rate limit breaches: `grep "RATE_LIMIT_EXCEEDED" storage/logs/*.log`

### Monthly
- [ ] Audit log summary: `SELECT action, COUNT(*) FROM omni_audit_logs GROUP BY action`
- [ ] Rotate API keys (Twilio, ElevenLabs, etc.)
- [ ] Review webhook failures: `SELECT * FROM omni_audit_logs WHERE error IS NOT NULL`

### Quarterly
- [ ] Purge old audit logs: `php artisan omni:purge-audit-logs`
- [ ] Review rate limit thresholds (adjust if too strict/loose)
- [ ] Rotate encryption key (when necessary): `php artisan omni:rotate-encryption`

---

## Next Steps

1. **Integrate hardening** (follow Step 1-10 above)
2. **Run verification checklist**
3. **Deploy to staging** and test
4. **Deploy to production**
5. **Monitor logs and metrics**
6. **Document any customizations**

---

## Support & Questions

For issues or questions during integration:
1. Check `SECURITY_HARDENING.md` for detailed explanations
2. Review test files for usage examples
3. Check inline code comments (every method is documented)
4. Consult the troubleshooting section above

