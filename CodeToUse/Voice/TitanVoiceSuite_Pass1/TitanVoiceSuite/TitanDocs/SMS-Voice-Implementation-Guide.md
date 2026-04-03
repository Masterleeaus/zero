# Adding Twilio SMS & Voice to Chatbot - Using Existing WhatsApp Pattern

## EXCELLENT NEWS 🎉

You already have **80% of what you need**. The WhatsApp extension uses Twilio's multi-channel architecture, and SMS + Voice follow the exact same pattern. This is **much simpler than initially thought**.

---

## EXISTING ARCHITECTURE (WhatsApp - What You Have)

```
Inbound WhatsApp Message → Twilio Webhook
                              ↓
                    ChatbotTwilioController::handle()
                              ↓
                    TwilioConversationService
                              ↓
                    GeneratorService (reuses existing)
                              ↓
                    TwilioWhatsappService::sendText()
                              ↓
                    Response sent back to user
```

**Key insight:** The `TwilioConversationService` is **channel-agnostic**. It just:
1. Receives a payload
2. Extracts: user message, phone number, message type
3. Stores conversation & message
4. Generates response
5. Sends back via channel-specific service

---

## PROPOSED ARCHITECTURE (Add SMS + Voice)

```
Inbound Message (WhatsApp/SMS/Voice) → Twilio Webhook
                              ↓
                    ChatbotTwilioController::handle()
                              ↓
                    TwilioConversationService (existing)
                              ↓
                    GeneratorService (existing)
                              ↓
            TwilioService (dispatcher)
            /              |              \
        sendText()    sendSms()      sendVoice()
       (WhatsApp)      (SMS)          (Voice Call)
```

---

## IMPLEMENTATION PLAN

### Phase 1: Create SMS Service (1-2 days)

Create a new SMS service file that mirrors the WhatsApp service:

**File:** `app/Extensions/ChatbotWhatsapp/System/Services/Twillio/TwilioSmsService.php`

```php
<?php

namespace App\Extensions\ChatbotWhatsapp\System\Services\Twillio;

use App\Extensions\Chatbot\System\Models\ChatbotChannel;
use Exception;
use Twilio\Rest\Client;

class TwilioSmsService
{
    public ChatbotChannel $chatbotChannel;

    public function sendText($message, $receiver)
    {
        $client = $this->client();

        // Get SMS-specific phone number from channel credentials
        $from = data_get($this->chatbotChannel['credentials'], 'sms_phone');

        try {
            $message = $client->messages->create(
                $receiver,
                [
                    'from' => $from,  // Regular phone number, not "whatsapp:" prefix
                    'body' => $message,
                ]
            );

            return [
                'properties' => $this->properties($message),
                'message'    => trans('SMS sent'),
                'status'     => true,
            ];
        } catch (Exception $exception) {
            return [
                'message' => $exception->getMessage(),
                'status'  => false,
            ];
        }
    }

    public function properties($message): array
    {
        return [
            'body'     => $message->__get('body'),
            'from'     => $message->__get('from'),
            'to'       => $message->__get('to'),
            'status'   => $message->__get('status'),
            'sid'      => $message->__get('sid'),
            'dateSent' => $message->__get('dateSent'),
        ];
    }

    public function client(): Client
    {
        $username = data_get($this->chatbotChannel['credentials'], 'sms_sid');
        $password = data_get($this->chatbotChannel['credentials'], 'sms_token');

        return new Client($username, $password);
    }

    public function getChatbotChannel(): ChatbotChannel
    {
        return $this->chatbotChannel;
    }

    public function setChatbotChannel(ChatbotChannel $chatbotChannel): self
    {
        $this->chatbotChannel = $chatbotChannel;
        return $this;
    }
}
```

---

### Phase 2: Create Voice Service (2-3 days)

**File:** `app/Extensions/ChatbotWhatsapp/System/Services/Twillio/TwilioVoiceService.php`

```php
<?php

namespace App\Extensions\ChatbotWhatsapp\System\Services\Twillio;

use App\Extensions\Chatbot\System\Models\ChatbotChannel;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use Exception;
use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;

class TwilioVoiceService
{
    public ChatbotChannel $chatbotChannel;

    /**
     * Handle incoming voice call
     * Returns TwiML (Twilio Markup Language) for call flow
     */
    public function handleIncomingCall(ChatbotConversation $conversation, string $greeting): string
    {
        $response = new VoiceResponse();

        // Play greeting message as audio
        $response->say($greeting, [
            'voice' => 'alice',  // Alice, man, woman, etc.
            'language' => 'en-US',
        ]);

        // Gather speech input (user speaking)
        $gather = $response->gather([
            'numDigits' => 1,
            'action' => route('api.v2.chatbot.voice.transcript', [
                'chatbot' => $conversation->chatbot->uuid,
                'conversation' => $conversation->id,
                'channelId' => $this->chatbotChannel->id,
            ]),
            'method' => 'POST',
            'speechTimeout' => 'auto',
            'speechModel' => 'numbers_and_commands',  // or 'default' for natural speech
            'language' => 'en-US',
        ]);

        // If no input, loop back
        if ($gather === null) {
            $response->redirect(route('api.v2.chatbot.voice.inbound', [
                'chatbot' => $conversation->chatbot->uuid,
                'conversation' => $conversation->id,
                'channelId' => $this->chatbotChannel->id,
            ]));
        }

        return $response->asXml();
    }

    /**
     * Send voice response to caller
     */
    public function playResponse(
        ChatbotConversation $conversation,
        string $responseText
    ): string
    {
        $response = new VoiceResponse();

        // Play AI response as audio
        $response->say($responseText, [
            'voice' => 'alice',
            'language' => 'en-US',
        ]);

        // Gather next input
        $gather = $response->gather([
            'numDigits' => 1,
            'action' => route('api.v2.chatbot.voice.transcript', [
                'chatbot' => $conversation->chatbot->uuid,
                'conversation' => $conversation->id,
                'channelId' => $this->chatbotChannel->id,
            ]),
            'method' => 'POST',
            'speechTimeout' => 'auto',
            'language' => 'en-US',
        ]);

        return $response->asXml();
    }

    /**
     * End call
     */
    public function hangup(): string
    {
        $response = new VoiceResponse();
        $response->hangup();
        return $response->asXml();
    }

    /**
     * Make outbound voice call
     */
    public function makeCall(string $toNumber, string $greeting): array
    {
        try {
            $client = $this->client();

            $from = data_get($this->chatbotChannel['credentials'], 'voice_phone');

            $call = $client->calls->create(
                $toNumber,
                $from,
                [
                    'twiml' => $this->handleIncomingCall(
                        app('conversation'), // Inject conversation context
                        $greeting
                    ),
                ]
            );

            return [
                'status'  => true,
                'call_sid' => $call->sid,
                'message' => trans('Voice call initiated'),
            ];
        } catch (Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function client(): Client
    {
        $username = data_get($this->chatbotChannel['credentials'], 'voice_sid');
        $password = data_get($this->chatbotChannel['credentials'], 'voice_token');

        return new Client($username, $password);
    }

    public function setChatbotChannel(ChatbotChannel $chatbotChannel): self
    {
        $this->chatbotChannel = $chatbotChannel;
        return $this;
    }
}
```

---

### Phase 3: Extend Conversation Service (1-2 days)

Update `TwilioConversationService` to handle SMS and Voice:

**File:** `app/Extensions/ChatbotWhatsapp/System/Services/Twillio/TwilioConversationService.php`

Add these methods:

```php
public function handleSms(): void
{
    $twilio = app(TwilioSmsService::class)
        ->setChatbotChannel(ChatbotChannel::find($this->channelId));

    $phoneNumber = data_get($this->payload, 'From');
    $messageBody = data_get($this->payload, 'Body');

    // Same logic as WhatsApp, but use SMS service
    $this->processSmsMessage($messageBody, $this->conversation, $this->chatbot, $twilio, $phoneNumber);
}

public function handleVoice(ChatbotConversation $conversation): string
{
    $twilio = app(TwilioVoiceService::class)
        ->setChatbotChannel(ChatbotChannel::find($this->channelId));

    // Record call info
    $conversation->update([
        'chatbot_channel' => 'voice',
        'call_phone_number' => data_get($this->payload, 'From'),
        'call_status' => 'connected',
        'call_started_at' => now(),
    ]);

    // Return TwiML for voice flow
    return $twilio->handleIncomingCall(
        $conversation,
        $conversation->chatbot->welcome_message ?? 'Hello, how can I help?'
    );
}

protected function processSmsMessage(
    string $messageBody,
    ChatbotConversation $conversation,
    Chatbot $chatbot,
    TwilioSmsService $twilio,
    string $phoneNumber
): void
{
    $response = $this->generateResponse($messageBody) ?? trans("Sorry, I can't answer right now.");

    $twilio->sendText($response, $phoneNumber);
    $this->insertMessage($conversation, $response, 'assistant', $chatbot->ai_model);
}
```

---

### Phase 4: Update Controller to Route by Channel Type (1 day)

**File:** `app/Extensions/ChatbotWhatsapp/System/Http/Controllers/Webhook/ChatbotTwilioController.php`

```php
<?php

namespace App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook;

use App\Extensions\Chatbot\System\Models\ChatbotChannelWebhook;
use App\Extensions\ChatbotWhatsapp\System\Services\Twillio\TwilioConversationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatbotTwilioController extends Controller
{
    public function __construct(
        public TwilioConversationService $service
    ) {}

    /**
     * Route to appropriate handler based on message type
     */
    public function handle(
        int $chatbotId,
        int $channelId,
        Request $request
    ) {
        // Determine channel type from payload
        $channelType = $this->detectChannelType($request);

        if (!$request->get('SmsSid') && !$request->get('CallSid')) {
            return [
                'status' => false,
            ];
        }

        // Store webhook for audit trail
        ChatbotChannelWebhook::query()->create([
            'chatbot_id'         => $chatbotId,
            'chatbot_channel_id' => $channelId,
            'payload'            => $request->all(),
            'channel_type'       => $channelType,
            'created_at'         => now(),
        ]);

        $this->service
            ->setIpAddress()
            ->setChatbotId($chatbotId)
            ->setChannelId($channelId)
            ->setPayload($request->all());

        $conversation = $this->service->storeConversation();
        $chatbot = $this->service->getChatbot();

        // Route based on channel type
        return match ($channelType) {
            'whatsapp' => $this->handleWhatsapp($conversation, $chatbot),
            'sms'      => $this->handleSms($conversation, $chatbot),
            'voice'    => $this->handleVoice($conversation, $chatbot),
            default    => ['status' => false],
        };
    }

    protected function detectChannelType(Request $request): string
    {
        // WhatsApp messages have 'WaId'
        if ($request->has('WaId')) {
            return 'whatsapp';
        }

        // Voice calls have 'CallSid'
        if ($request->has('CallSid')) {
            return 'voice';
        }

        // SMS has 'From', 'To', 'SmsSid'
        if ($request->has('SmsSid')) {
            return 'sms';
        }

        return 'unknown';
    }

    protected function handleWhatsapp($conversation, $chatbot)
    {
        // Existing WhatsApp logic
        $this->service->insertMessage(
            conversation: $conversation,
            message: request()->get('Body') ?? '',
            role: 'user',
            model: $chatbot?->getAttribute('ai_model')
        );

        $this->service->handleWhatsapp();

        return response()->json(['status' => true]);
    }

    protected function handleSms($conversation, $chatbot)
    {
        // SMS logic
        $this->service->insertMessage(
            conversation: $conversation,
            message: request()->get('Body') ?? '',
            role: 'user',
            model: $chatbot?->getAttribute('ai_model')
        );

        $this->service->handleSms();

        return response()->json(['status' => true]);
    }

    protected function handleVoice($conversation, $chatbot)
    {
        // Voice call logic - returns TwiML XML
        $twiml = $this->service->handleVoice($conversation);

        return response($twiml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
```

---

### Phase 5: Update Routes (1 day)

Add new webhook routes in the Service Provider:

```php
// In ChatbotWhatsappServiceProvider or ChatbotServiceProvider

$router->group([
    'middleware' => ['api'],
    'prefix'     => 'api/v2/chatbot',
    'as'         => 'api.v2.chatbot.',
], function (Router $router) {
    
    // WhatsApp webhook (existing)
    $router->post(
        'channel/twilio/{chatbotId}/{channelId}',
        [ChatbotTwilioController::class, 'handle']
    )->name('channel.twilio.post.handle');

    // SMS webhook (same endpoint, but detected by controller)
    // Voice webhook (same endpoint, but detected by controller)
    // All route to same controller, which detects type

    // New voice-specific endpoints for TwiML responses
    $router->post(
        '{chatbot:uuid}/voice/inbound',
        [VoiceCallController::class, 'inbound']
    )->name('voice.inbound');

    $router->post(
        '{chatbot:uuid}/voice/transcript',
        [VoiceCallController::class, 'transcript']
    )->name('voice.transcript');
});
```

---

### Phase 6: Update Channel Model (1 day)

Add SMS and Voice credentials to `ChatbotChannel` model:

```php
// Already exists, just add credentials support:
$table->json('credentials')->nullable(); // Stores:
// {
//   "whatsapp_sid": "...",
//   "whatsapp_token": "...",
//   "whatsapp_phone": "whatsapp:+1...",
//   "whatsapp_sandbox_phone": "...",
//   "sms_sid": "...",
//   "sms_token": "...",
//   "sms_phone": "+1...",
//   "voice_sid": "...",
//   "voice_token": "...",
//   "voice_phone": "+1...",
// }
```

---

### Phase 7: Dashboard UI Updates (2-3 days)

Create channel setup pages for SMS and Voice:

**Files to create:**
- `resources/views/channel-sms-card.blade.php`
- `resources/views/channel-voice-card.blade.php`
- Controllers for SMS/Voice channel setup

```blade
<!-- SMS Channel Setup Card -->
<div class="channel-card sms-card">
    <h3>SMS Chatbot</h3>
    <p>Connect via Twilio SMS</p>

    <input type="text" placeholder="Twilio Account SID" name="sms_sid">
    <input type="text" placeholder="Twilio Auth Token" name="sms_token">
    <input type="text" placeholder="Your SMS Phone Number" name="sms_phone">

    <button>Connect SMS</button>
</div>

<!-- Voice Channel Setup Card -->
<div class="channel-card voice-card">
    <h3>Voice Chatbot</h3>
    <p>Connect via Twilio Voice Calls</p>

    <input type="text" placeholder="Twilio Account SID" name="voice_sid">
    <input type="text" placeholder="Twilio Auth Token" name="voice_token">
    <input type="text" placeholder="Your Voice Phone Number" name="voice_phone">

    <button>Connect Voice</button>
</div>
```

---

## QUICK COMPARISON: What's Different?

| Aspect | WhatsApp | SMS | Voice |
|--------|----------|-----|-------|
| **Input** | Text via WhatsApp | Text via SMS | Speech (STT) |
| **Output** | Text via WhatsApp | Text via SMS | Speech (TTS) |
| **Message Format** | `whatsapp:+1...` | `+1...` | `+1...` |
| **Protocol** | REST API | REST API | TwiML (XML) |
| **Service Class** | `TwilioWhatsappService` | `TwilioSmsService` | `TwilioVoiceService` |
| **Conversation Flow** | Request-response | Request-response | Streaming/IVR |
| **Cost per message** | Variable | $0.0075 | $0.013/min |

---

## DATABASE SCHEMA (Minimal Changes)

Only need to extend `ChatbotChannel` and `ChatbotConversation`:

```sql
-- Already exists, just store different credentials
ALTER TABLE ext_chatbot_channels 
ADD COLUMN channel_type ENUM('whatsapp', 'sms', 'voice') DEFAULT 'whatsapp';

-- Already supports this
ALTER TABLE ext_chatbot_conversations
ADD COLUMN call_phone_number VARCHAR(20),
ADD COLUMN call_status VARCHAR(50),
ADD COLUMN call_started_at TIMESTAMP,
ADD COLUMN call_duration_seconds INT;
```

---

## IMPLEMENTATION TIMELINE (Realistic)

| Phase | Task | Days | Effort |
|-------|------|------|--------|
| 1 | SMS Service | 1-2 | Low |
| 2 | Voice Service + TwiML | 2-3 | Medium |
| 3 | Conversation Service Ext | 1-2 | Low |
| 4 | Controller Routing | 1 | Low |
| 5 | Routes & Webhooks | 1 | Low |
| 6 | Channel Model | 1 | Low |
| 7 | Dashboard UI | 2-3 | Medium |
| **Total** | **SMS + Voice** | **9-14 days** | **1-2 weeks** |

---

## AMAZING SIMPLIFICATION

Instead of the **2-4 weeks and 1000+ lines of code** I initially estimated, you now have:

✅ **Reusable conversation service** (already handles multi-channel)
✅ **Existing Twilio integration** (just extend it)
✅ **Proven webhook pattern** (WhatsApp is template)
✅ **Multi-engine AI** (GeneratorService works for all)
✅ **Agent handoff** (already implemented)

**Result: ~400-500 lines of actual new code needed, most is just copy-paste with slight modifications.**

---

## FILES TO CREATE/MODIFY

**New Files (SMS):**
```
app/Extensions/ChatbotWhatsapp/System/Services/Twillio/TwilioSmsService.php
```

**New Files (Voice):**
```
app/Extensions/ChatbotWhatsapp/System/Services/Twillio/TwilioVoiceService.php
app/Extensions/ChatbotWhatsapp/System/Http/Controllers/Webhook/VoiceCallController.php
```

**Modified Files:**
```
app/Extensions/ChatbotWhatsapp/System/Services/Twillio/TwilioConversationService.php
app/Extensions/ChatbotWhatsapp/System/Http/Controllers/Webhook/ChatbotTwilioController.php
app/Extensions/ChatbotWhatsapp/System/ChatbotWhatsappServiceProvider.php
```

**Dashboard Views:**
```
resources/views/chatbot-whatsapp/channel-sms-card.blade.php
resources/views/chatbot-whatsapp/channel-voice-card.blade.php
```

---

## WHY THIS WORKS SO WELL

1. **Twilio's unified API** — SMS, Voice, WhatsApp all use same SDK
2. **Your existing webhook pattern** — Already handles multi-channel payloads
3. **Stateless AI** — GeneratorService doesn't care what channel
4. **Message abstraction** — insertMessage() works for all types
5. **Channel detection** — Simple `detectChannelType()` routes correctly

---

## RISK: Nearly Zero

✅ No breaking changes to existing code
✅ SMS & Voice are **opt-in** via channel setup
✅ Same error handling patterns
✅ Same conversation model
✅ Easy to test each channel independently

---

## NEXT STEPS

1. **Create `TwilioSmsService.php`** — Copy WhatsApp service, modify for SMS
2. **Create `TwilioVoiceService.php`** — TwiML generation for voice calls
3. **Update `TwilioConversationService`** — Add `handleSms()` and `handleVoice()`
4. **Update routes** — Add SMS/Voice webhook endpoints
5. **Build dashboard UI** — Channel setup forms
6. **Test with Twilio sandbox** — Before going live

Want me to create the code files?
