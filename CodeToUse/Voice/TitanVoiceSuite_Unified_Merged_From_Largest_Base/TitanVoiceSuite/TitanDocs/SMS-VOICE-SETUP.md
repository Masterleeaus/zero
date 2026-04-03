# SMS & Voice Implementation - Setup Guide

## Quick Summary of Changes

You now have 5 new/updated files to integrate into your `ChatbotWhatsapp` extension:

### New Files
1. **TwilioSmsService.php** - SMS message sending
2. **TwilioVoiceService.php** - Voice call handling with TwiML
3. **VoiceCallController.php** - Voice webhook endpoints (transcript, status, recording)

### Modified Files
1. **TwilioConversationService.php** - Add SMS and Voice handlers
2. **ChatbotTwilioController.php** - Route SMS/Voice/WhatsApp to correct handler

### Database
1. **Migration** - Add voice/SMS columns to conversations & channels

---

## Installation Steps

### Step 1: Copy Service Files

Copy these files to your extension:

```
app/Extensions/ChatbotWhatsapp/System/Services/Twillio/TwilioSmsService.php
app/Extensions/ChatbotWhatsapp/System/Services/Twillio/TwilioVoiceService.php
```

### Step 2: Copy Controller Files

```
app/Extensions/ChatbotWhatsapp/System/Http/Controllers/Webhook/VoiceCallController.php
```

### Step 3: Update Existing Files

**TwilioConversationService.php** (replace entire file)
```
app/Extensions/ChatbotWhatsapp/System/Services/Twillio/TwilioConversationService.php
```

**ChatbotTwilioController.php** (replace entire file)
```
app/Extensions/ChatbotWhatsapp/System/Http/Controllers/Webhook/ChatbotTwilioController.php
```

### Step 4: Run Migration

Copy migration to your database migrations folder:
```
database/migrations/2026_04_01_000000_add_sms_voice_support.php
```

Then run:
```bash
php artisan migrate
```

### Step 5: Update Routes

Update `ChatbotWhatsappServiceProvider.php` in the `registerRoutes()` method.

Replace the existing Twilio webhook route with the new one from `ROUTES_TO_ADD.php`:

```php
// Old route:
$router->post('channel/twilio/{chatbotId}/{channelId}', ...)->name('channel.twilio.post.handle');

// New routes (add these):
$router->post('channel/twilio/{chatbotId}/{channelId}', [ChatbotTwilioController::class, 'handle'])->name('channel.twilio.post.handle');
$router->post('{chatbot:uuid}/voice/transcript/{conversation}/{channelId}', [VoiceCallController::class, 'transcript'])->name('voice.transcript');
$router->post('{chatbot:uuid}/voice/status/{conversation}/{channelId}', [VoiceCallController::class, 'statusCallback'])->name('voice.status');
$router->post('{chatbot:uuid}/voice/recording/{conversation}/{channelId}', [VoiceCallController::class, 'recordingCallback'])->name('voice.recording');
$router->post('{chatbot:uuid}/voice/end/{conversation}/{channelId}', [VoiceCallController::class, 'endCall'])->name('voice.end');
```

Also add imports at the top:
```php
use App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook\ChatbotTwilioController;
use App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook\VoiceCallController;
```

---

## Twilio Configuration

### Create Twilio Account & Get Credentials

1. **Sign up at:** https://www.twilio.com/console
2. **Get your credentials:**
   - Account SID
   - Auth Token
   - Phone numbers (one each for SMS and Voice)

### SMS Setup

1. Get an SMS-capable phone number from Twilio
2. Note the credentials:
   - SMS SID (same as Account SID)
   - SMS Token (same as Auth Token)
   - SMS Phone Number (e.g., +1234567890)

### Voice Setup

1. Get a voice-capable phone number from Twilio
2. Note the credentials:
   - Voice SID (same as Account SID)
   - Voice Token (same as Auth Token)
   - Voice Phone Number (e.g., +1987654321)

### Configure Webhook URLs in Twilio

For SMS, set the webhook in Twilio console:
```
Webhook URL: https://yourdomain.com/api/v2/chatbot/channel/twilio/{chatbotId}/{channelId}
Webhook Method: POST
```

For Voice, set the webhook (gets URL from incoming call):
```
Handle incoming calls with TwiML
Request URL: https://yourdomain.com/api/v2/chatbot/channel/twilio/{chatbotId}/{channelId}
```

---

## Dashboard UI (Optional but Recommended)

You'll want to add UI for users to configure SMS and Voice channels.

Create these view files:

### SMS Channel Card
**File:** `resources/views/chatbot-whatsapp/channel-sms-card.blade.php`

```blade
<div class="channel-card sms-channel">
    <div class="card-header">
        <h3>SMS Chatbot</h3>
        <p>Connect via Twilio SMS</p>
    </div>

    <form action="{{ route('dashboard.chatbot-channel.store') }}" method="POST">
        @csrf
        <input type="hidden" name="channel_type" value="sms">

        <div class="form-group">
            <label>Twilio Account SID</label>
            <input type="text" name="sms_sid" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Twilio Auth Token</label>
            <input type="password" name="sms_token" class="form-control" required>
        </div>

        <div class="form-group">
            <label>SMS Phone Number</label>
            <input type="tel" name="sms_phone" class="form-control" placeholder="+1234567890" required>
        </div>

        <button type="submit" class="btn btn-primary">Connect SMS</button>
    </form>
</div>
```

### Voice Channel Card
**File:** `resources/views/chatbot-whatsapp/channel-voice-card.blade.php`

```blade
<div class="channel-card voice-channel">
    <div class="card-header">
        <h3>Voice Chatbot</h3>
        <p>Connect via Twilio Voice Calls</p>
    </div>

    <form action="{{ route('dashboard.chatbot-channel.store') }}" method="POST">
        @csrf
        <input type="hidden" name="channel_type" value="voice">

        <div class="form-group">
            <label>Twilio Account SID</label>
            <input type="text" name="voice_sid" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Twilio Auth Token</label>
            <input type="password" name="voice_token" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Voice Phone Number</label>
            <input type="tel" name="voice_phone" class="form-control" placeholder="+1987654321" required>
        </div>

        <div class="form-group">
            <label>Voice Settings</label>
            <div class="checkbox">
                <input type="checkbox" name="enable_recording" value="1">
                <label>Enable call recording</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Connect Voice</button>
    </form>
</div>
```

---

## Testing

### Test with Twilio Sandbox

1. **SMS Sandbox:**
   - Use Twilio sandbox phone number (temporary)
   - Send test SMS
   - Verify webhook receives payload

2. **Voice Sandbox:**
   - Use Twilio sandbox number (temporary)
   - Make test call
   - Verify webhook receives CallSid

### Test Flows

**SMS Flow:**
```
1. Send SMS to your SMS number
   → Webhook: SmsSid, Body, From
2. ChatbotTwilioController detects SMS
3. TwilioConversationService.handleSms()
4. GeneratorService generates response
5. TwilioSmsService.sendText() sends reply
```

**Voice Flow:**
```
1. Call your voice number
   → Webhook: CallSid, From
2. ChatbotTwilioController detects voice
3. TwilioConversationService.handleVoice()
4. Returns TwiML (voice greeting + gather)
5. User speaks
6. VoiceCallController.transcript() receives speech
7. GeneratorService generates response
8. TwilioVoiceService.playResponse() returns TwiML
9. Repeat until call ends
```

---

## Credentials Storage in Database

The `ChatbotChannel` model stores credentials as JSON in the `credentials` column:

```json
{
  "whatsapp_sid": "ACxxxxxxxxxxxxxx",
  "whatsapp_token": "your_twilio_token",
  "whatsapp_phone": "whatsapp:+15551234567",
  "whatsapp_sandbox_phone": "whatsapp:+15555555555",
  "sms_sid": "ACxxxxxxxxxxxxxx",
  "sms_token": "your_twilio_token",
  "sms_phone": "+15551234567",
  "voice_sid": "ACxxxxxxxxxxxxxx",
  "voice_token": "your_twilio_token",
  "voice_phone": "+15559876543"
}
```

This is accessed via:
```php
data_get($chatbotChannel['credentials'], 'sms_phone')
data_get($chatbotChannel['credentials'], 'voice_phone')
```

---

## Error Handling

All services catch exceptions and return status/message arrays:

```php
return [
    'status'  => true/false,
    'message' => 'Error description',
];
```

Check logs at: `storage/logs/laravel.log`

---

## Costs Breakdown (Twilio 2024)

| Channel | Type | Cost | Example |
|---------|------|------|---------|
| SMS | Inbound | $0.0075/msg | 100 msgs/month = $0.75 |
| SMS | Outbound | $0.0075/msg | 100 msgs/month = $0.75 |
| Voice | Inbound | $0.013/min | 1000 min/month = $13 |
| Voice | Outbound | $0.013/min | 1000 min/month = $13 |
| Phone # | Monthly | $1/month | $1 per number |

**Total Budget for 1000 interactions/month:**
- ~100 SMS interactions: $1.50
- ~100 Voice calls (avg 3 min): $39
- 2 phone numbers: $2
- **Total: ~$42/month**

---

## Key Features Enabled

✅ **SMS Chatbot** - Full 2-way SMS conversations
✅ **Voice Calls** - IVR-style voice interactions
✅ **Multi-channel** - Same chatbot across SMS/Voice/WhatsApp
✅ **Agent Handoff** - Transfer to human on all channels
✅ **Call Tracking** - Recording, duration, status
✅ **Transcript Logging** - All conversations stored
✅ **Smart Routing** - Auto-detect channel type

---

## Troubleshooting

### SMS Not Working
1. Check Twilio account has SMS credits
2. Verify phone number is SMS-capable
3. Check webhook URL in Twilio console
4. Look for errors in `storage/logs/laravel.log`

### Voice Calls Not Working
1. Check Twilio account has voice credits
2. Verify phone number is voice-capable
3. Check webhook URL accepts POST with TwiML response
4. Verify speech recognition language matches chatbot language

### Webhook Not Being Called
1. Verify domain is publicly accessible (not localhost)
2. Check Twilio webhook URL is exact match
3. Verify request is coming from Twilio IP ranges
4. Check Laravel CSRF middleware isn't blocking (use API middleware)

---

## Environment Variables (Optional)

If you want to store credentials in `.env` instead of database:

```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_token_here
TWILIO_SMS_PHONE=+15551234567
TWILIO_VOICE_PHONE=+15559876543
TWILIO_TTS_VOICE=alice
TWILIO_LANGUAGE=en-US
```

Then access via:
```php
env('TWILIO_SMS_PHONE')
env('TWILIO_VOICE_PHONE')
```

---

## Next Steps

1. ✅ Copy files to extension
2. ✅ Run migration
3. ✅ Update routes in ServiceProvider
4. ✅ Create Twilio account
5. ✅ Get credentials
6. ✅ Configure webhook URLs
7. ✅ Test with sandbox
8. ✅ (Optional) Build dashboard UI
9. ✅ Go live

Good luck! 🚀
