# Converting External-Chatbot to Inbound Phone Call Support

## EXECUTIVE SUMMARY

**Feasibility: YES, but with moderate effort.** The architecture is clean enough to add phone call integration, but it would require:
- A new VOIP provider integration module (Twilio, Vonage, Asterisk, etc.)
- Webhook handlers for inbound call events
- Voice-to-text and text-to-voice adapters
- Database schema extensions (~2-3 new tables)
- Frontend integration changes
- Estimated effort: **2-4 weeks** for a production-ready implementation

---

## CURRENT ARCHITECTURE ASSESSMENT

### Strengths (Makes Conversion Easy)

1. **Clean Service Layer**
   - `GeneratorService` already abstracts AI engine selection
   - `ChatbotService` handles business logic cleanly
   - Easy to inject a phone call handler without breaking existing code

2. **Pluggable Provider Pattern Already Exists**
   - `voice_call_provider` setting already in place for provider selection
   - Multi-engine routing (OpenAI, Claude, Gemini, DeepSeek, X.AI) is proven pattern
   - Can reuse this for VOIP providers

3. **Message Flow Already Abstraction-Friendly**
   ```
   User Input → insertMessage() → GeneratorService → Response → insertMessage()
   ```
   This flow works whether input is:
   - Text from web widget (current)
   - Voice transcript from phone call (new)
   - Audio stream from Twilio webhook (new)

4. **Flexible Message Types**
   - Already supports: text, user voice transcripts, assistant voice transcripts, media
   - Can easily add: `phone_call`, `voice_call_duration`, `call_initiated_by`

5. **Customer Tracking Already In Place**
   - `ChatbotCustomer` model stores phone number
   - Session/conversation tracking is flexible enough for phone calls

6. **Agent Handoff Already Exists**
   - `connect_agent_at` and `human_agent_conditions` logic already implemented
   - Easy to add phone transfer capability

### Weaknesses (Would Need Work)

1. **No Phone Provider Integration**
   - No Twilio/Vonage/Asterisk handlers currently
   - Would need to write webhook handlers for inbound calls
   - Estimated: 1-2 weeks

2. **No Real-Time Call Management**
   - Current architecture is request-response (stateless)
   - Phone calls need persistent, streaming connections
   - Would need WebSocket or polling for call state

3. **No IVR (Interactive Voice Response)**
   - No DTMF handling (button presses during call)
   - No call menu system
   - No voicemail capture
   - Estimated add-on: 1 week (if needed)

4. **Voice Transcript Dependency**
   - Currently relies on external `chatbot-voice-call` extension
   - That extension likely uses browser APIs (Web Speech API) which won't work on VOIP
   - Would need server-side speech-to-text (Twilio transcription, OpenAI Whisper, etc.)
   - Estimated: 3-5 days

5. **Text-to-Speech for Phone**
   - Current TTS likely optimized for web playback
   - Phone systems need different format (μlaw, alaw, G.729)
   - Twilio/Vonage have built-in TTS that could work
   - Estimated: 2-3 days

6. **Call State Management Not Present**
   - No concept of "call in progress", "call ended", "transferred"
   - Would need to extend `ChatbotConversation` model
   - Add columns: `call_status`, `call_started_at`, `call_ended_at`, `call_transferred_at`

7. **No Queue/Callback System**
   - If all agents busy, can't queue callers or offer callbacks
   - Would need additional infrastructure
   - Nice-to-have, not essential

---

## IMPLEMENTATION ROADMAP

### Phase 1: Foundation (3-5 days)
**Goals:** Basic inbound call acceptance and response

1. **Create `VoiceProvider` Interface**
   ```php
   // app/Extensions/Chatbot/System/Providers/Contracts/VoiceProvider.php
   interface VoiceProvider {
       public function handleInboundCall(Request $request);
       public function recordCall(ChatbotConversation $conversation, array $data);
       public function transferToAgent(ChatbotConversation $conversation, User $agent);
       public function hangupCall(ChatbotConversation $conversation, string $reason);
   }
   ```

2. **Create Twilio Implementation** (fastest path)
   ```php
   // app/Extensions/Chatbot/System/Providers/TwilioVoiceProvider.php
   class TwilioVoiceProvider implements VoiceProvider {
       // Webhook handler for incoming calls
       // Call state tracking
       // TTS/STT integration
   }
   ```

3. **Add Webhook Routes**
   ```
   POST /api/v2/chatbot/{uuid}/voice/inbound
   POST /api/v2/chatbot/{uuid}/voice/status-callback
   POST /api/v2/chatbot/{uuid}/voice/transcript
   ```

4. **Extend Database**
   ```sql
   ALTER TABLE ext_chatbot_conversations 
   ADD COLUMN call_phone_number VARCHAR(20),
   ADD COLUMN call_status ENUM('incoming','connected','ended','transferred'),
   ADD COLUMN call_started_at TIMESTAMP,
   ADD COLUMN call_ended_at TIMESTAMP,
   ADD COLUMN call_duration_seconds INT,
   ADD COLUMN call_provider VARCHAR(50);
   ```

5. **Create Call Handler Service**
   ```php
   // app/Extensions/Chatbot/System/Services/PhoneCallService.php
   class PhoneCallService {
       public function initiateCall(Chatbot $chatbot, string $fromNumber, string $toNumber)
       public function receiveCall(Chatbot $chatbot, Request $webhookData)
       public function recordTranscript(ChatbotConversation $conversation, string $transcript)
       public function generateVoiceResponse(ChatbotConversation $conversation, string $text)
       public function transferToAgent(ChatbotConversation $conversation, User $agent)
       public function endCall(ChatbotConversation $conversation)
   }
   ```

### Phase 2: Voice Intelligence (1 week)
**Goals:** Real-time transcription and response generation

1. **Integrate Twilio Transcription**
   - Receive real-time transcripts via callback
   - Store as `voice-transcript-user` message type
   - Route through existing `GeneratorService`

2. **Add Speech Synthesis**
   - Generate TTS for AI responses
   - Use Twilio TTS or OpenAI TTS
   - Stream audio back to caller

3. **Call State Machine**
   ```
   incoming → ringing → connected → (transcript processing)
   → (AI generating response) → (TTS playing) → listening → ...
   → transfer/hangup
   ```

### Phase 3: Agent Handoff (3-5 days)
**Goals:** Transfer to human agents

1. **Extend Agent System**
   - Add agent `phone_number` or SIP URI
   - Add agent `availability` for phone calls
   - Add bridge/transfer logic

2. **Transfer Logic**
   ```php
   // When human needed:
   $phoneCallService->transferToAgent($conversation, $agent);
   // Twilio conference bridge or blind transfer
   ```

3. **Agent Controls**
   - Accept/reject incoming call
   - Hold call
   - Transfer to another agent
   - End call

### Phase 4: Polish & Features (1 week)
**Goals:** Production readiness

1. **Caller ID Verification**
2. **Call Recording** (with consent)
3. **Voicemail Fallback**
4. **Call Logs & Analytics**
5. **GDPR Compliance** (already partially there)
6. **Error Handling & Retry Logic**
7. **Rate Limiting for Outbound Calls**

---

## TECHNICAL ARCHITECTURE

### New Components

```
┌─────────────────────────────────────────────────────────────┐
│                      Phone Call Flow                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Twilio Webhook → PhoneCallService → VoiceProvider         │
│                        ↓                                     │
│                 Receive → STT → insertMessage()             │
│                        ↓                                     │
│              GeneratorService (existing) ← AI               │
│                        ↓                                     │
│            TTS → Play Audio → Listen for Response           │
│                        ↓                                     │
│         Check if human needed (existing logic)              │
│                        ↓                                     │
│              Transfer to Agent OR Continue                  │
│                        ↓                                     │
│                   End Call & Log                            │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### New Database Tables

```sql
-- Phone call logs (optional, for detailed analytics)
CREATE TABLE ext_chatbot_call_logs (
    id BIGINT PRIMARY KEY,
    chatbot_id BIGINT,
    conversation_id BIGINT,
    call_sid VARCHAR(255),  -- Twilio Call SID
    from_number VARCHAR(20),
    to_number VARCHAR(20),
    call_status VARCHAR(50),
    call_duration_seconds INT,
    transferred_to_agent_id BIGINT,
    recording_url VARCHAR(500),
    created_at TIMESTAMP,
    ended_at TIMESTAMP
);

-- Extend ext_chatbot_conversations (already partially done):
-- call_phone_number, call_status, call_started_at, etc.
```

### New Controllers

```php
// API
app/Extensions/Chatbot/System/Http/Controllers/Api/PhoneCallWebhookController.php
  - handleInboundCall()
  - handleStatusCallback()
  - handleTranscript()

// Dashboard
app/Extensions/Chatbot/System/Http/Controllers/PhoneCallConfigController.php
  - Show phone settings
  - Configure Twilio credentials
  - View call analytics
```

### New Services

```php
app/Extensions/Chatbot/System/Services/PhoneCallService.php
app/Extensions/Chatbot/System/Services/PhoneTranscriptionService.php
app/Extensions/Chatbot/System/Services/PhoneSynthesisService.php
app/Extensions/Chatbot/System/Providers/VoiceProviders/TwilioProvider.php
app/Extensions/Chatbot/System/Providers/VoiceProviders/VonageProvider.php
```

---

## CODE EXAMPLES

### 1. Add Phone Call Handler

```php
// routes in ChatbotServiceProvider
->group([
    'middleware' => ['api'],
    'prefix'     => 'api/v2/chatbot/voice',
    'as'         => 'api.v2.chatbot.voice.',
    'controller' => PhoneCallWebhookController::class,
], function (Router $router) {
    $router->post('{chatbot:uuid}/inbound', 'handleInbound')->name('inbound');
    $router->post('{chatbot:uuid}/status', 'statusCallback')->name('status');
    $router->post('{chatbot:uuid}/transcript', 'transcript')->name('transcript');
});
```

### 2. Phone Call Service Integration

```php
class PhoneCallService {
    public function __construct(
        public GeneratorService $generatorService,
        public PhoneSynthesisService $synthesis,
        public VoiceProvider $provider,
    ) {}

    public function receiveCall(Chatbot $chatbot, Request $request): Response
    {
        // Create conversation for phone call
        $conversation = ChatbotConversation::create([
            'chatbot_id'        => $chatbot->id,
            'chatbot_channel'   => 'phone',
            'call_phone_number' => $request->input('From'),
            'call_status'       => 'connected',
            'call_started_at'   => now(),
        ]);

        // Play welcome message
        $greeting = $chatbot->welcome_message 
            ?? "Hello, how can I help?";
        
        $twiml = $this->synthesis->textToSpeech($greeting);

        return response($twiml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function processTranscript(
        Chatbot $chatbot, 
        ChatbotConversation $conversation,
        string $transcript
    ): string
    {
        // Store user message
        $this->insertMessage(
            conversation: $conversation,
            message: $transcript,
            role: 'user',
            message_type: 'voice-transcript-user',
        );

        // Generate response (reuse existing logic)
        $response = $this->generatorService
            ->setChatbot($chatbot)
            ->setConversation($conversation)
            ->setPrompt($transcript)
            ->generate();

        // Store assistant response
        $this->insertMessage(
            conversation: $conversation,
            message: $response,
            role: 'assistant',
            message_type: 'voice-transcript-assistant',
        );

        // Check if human needed
        if ($this->needsHumanAgent($chatbot, $response)) {
            return $this->provider->initiateTransfer($conversation);
        }

        // Convert to speech and return TwiML
        return $this->synthesis->textToSpeech($response);
    }
}
```

### 3. Twilio Provider Implementation

```php
class TwilioVoiceProvider implements VoiceProvider {
    public function __construct(
        private Twilio $twilio,
        private PhoneSynthesisService $synthesis,
    ) {}

    public function handleInboundCall(Request $request): Response
    {
        $fromNumber = $request->input('From');
        $callSid = $request->input('CallSid');

        // Find chatbot by phone number (or from URL)
        $chatbot = Chatbot::where('phone_number', $request->route('uuid'))->first();

        $service = app(PhoneCallService::class);
        return $service->receiveCall($chatbot, $request);
    }

    public function transferToAgent(
        ChatbotConversation $conversation,
        User $agent
    ): void
    {
        $client = $this->twilio->getClient();

        // Create conference or blind transfer
        $client->calls($conversation->call_sid)->update([
            'twiml' => '<Response>
                <Dial>
                    <SipEndpoint>' . $agent->sip_uri . '</SipEndpoint>
                </Dial>
            </Response>',
        ]);

        $conversation->update([
            'connect_agent_at'         => now(),
            'call_status'              => 'transferred',
            'call_transferred_at'      => now(),
            'transfer_to_agent_id'     => $agent->id,
        ]);
    }

    public function recordCall(string $callSid, string $recordingUrl): void
    {
        // Store recording URL in conversation or dedicated table
        ChatbotCallLog::create([
            'call_sid'      => $callSid,
            'recording_url' => $recordingUrl,
        ]);
    }
}
```

---

## CONFIGURATION ADDITIONS

### Settings to Add

```php
// Add to settings table or .env
VOICE_CALL_PROVIDER=twilio  // or vonage, asterisk
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_PHONE_NUMBER=+1234567890
TWILIO_TTS_VOICE=alice      // alice, man, woman, etc.
VOICE_CALL_TRANSCRIPTION_ENGINE=twilio  // twilio, openai_whisper
VOICE_CALL_SYNTHESIS_ENGINE=twilio      // twilio, google, openai
VOICE_CALL_MAX_DURATION=600             // max call length in seconds
VOICE_CALL_ENABLE_RECORDING=true
VOICE_CALL_RECORD_BOTH_SIDES=true
```

### Chatbot Settings UI

Add new section in chatbot configuration:
```
[ ] Enable Phone Calls
Phone Number: _____
Incoming Call Greeting: ________
Max Call Duration: [___] seconds
Enable Recording: [ ]
Transcription Engine: [Twilio ▼]
TTS Engine: [Twilio ▼]
Transfer to Agent on: [ ] Specific keywords [ ] Confidence threshold
```

---

## DEPENDENCIES

### New Composer Packages

```bash
composer require twilio/sdk              # Twilio SDK
composer require openai-php/client       # For Whisper STT
composer require symfony/process         # For async operations
```

### Alternative Providers (Drop-in Replacements)

- **Vonage (Nexmo)** - Similar architecture, slightly different API
- **Asterisk** - Open-source, more complex self-hosted setup
- **OpenPhone** - Lightweight, good for startups
- **Bandwidth.com** - Enterprise-grade

---

## RISK ASSESSMENT

### Low Risk
✅ Reuses existing message flow (insertMessage, GeneratorService)
✅ Clean separation of concerns (VoiceProvider interface)
✅ No changes to existing chat functionality
✅ Can be deployed as separate module/extension

### Medium Risk
⚠️ Real-time call state management (need WebSocket or long-polling)
⚠️ Concurrent calls might stress GeneratorService (add queuing)
⚠️ Phone number privacy/security (needs GDPR updates)

### Mitigation
- Queue long-running AI generation tasks
- Add call timeout safeguards
- Implement rate limiting per chatbot
- Encrypt stored phone numbers
- Add call recording consent flow

---

## COST IMPLICATIONS

### Twilio Pricing (2024 estimates)
- **Incoming calls:** $0.0075/min
- **Outgoing calls:** $0.013/min
- **Phone number:** $1/month
- **Transcription:** $0.0001/sec additional

### Budget Example
- 1000 calls/month × 3 min average × $0.0075 = **$22.50**
- Plus phone number ($1) + transcription (~$5) = **~$30/month**

---

## IMPLEMENTATION TIMELINE

| Phase | Effort | Timeline |
|-------|--------|----------|
| Foundation | 1 week | Weeks 1-2 |
| Voice Intelligence | 1 week | Weeks 2-3 |
| Agent Handoff | 3-5 days | Weeks 3-4 |
| Polish & Testing | 3-5 days | Weeks 4-5 |
| **Total** | **2-4 weeks** | **Month 1** |

---

## NEXT STEPS

If you want to proceed:

1. **Choose VOIP Provider** → Twilio (easiest), Vonage, or Asterisk
2. **Get API Keys** → Sign up and get credentials
3. **Create Feature Branch** → `feature/phone-call-integration`
4. **Start with Phase 1** → Basic inbound call handler
5. **Test with Twilio Sandbox** → Before production

Would you like me to:
- Create the initial `VoiceProvider` interface?
- Build the `PhoneCallService`?
- Create the Twilio webhook handler?
- Set up the database migrations?

---

## QUESTIONS TO ANSWER FIRST

Before starting, clarify:

1. **Inbound only, or outbound too?** (Currently designed for inbound)
2. **Which VOIP provider?** (Twilio = fastest path)
3. **Voicemail support?** (Optional add-on)
4. **Call recording?** (Legal/compliance implications)
5. **Multi-agent conference calls?** (Nice-to-have)
6. **IVR/menu system?** (More complex, not needed initially)
7. **Analytics/reporting?** (Can be added in Phase 4)
8. **Geographic region?** (Affects phone number availability)
