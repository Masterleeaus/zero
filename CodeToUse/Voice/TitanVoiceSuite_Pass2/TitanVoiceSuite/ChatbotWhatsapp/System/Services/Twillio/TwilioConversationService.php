<?php

namespace App\Extensions\ChatbotWhatsapp\System\Services\Twillio;

use App\Extensions\Chatbot\System\Enums\InteractionType;
use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotChannel;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use App\Extensions\Chatbot\System\Services\GeneratorService;
use App\Extensions\ChatbotAgent\System\Services\ChatbotForPanelEventAbly;
use App\Helpers\Classes\Helper;
use App\Helpers\Classes\MarketplaceHelper;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Extensions\ChatbotWhatsapp\System\Models\VoiceCommandLog;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\ContextManager;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\IntentRouter;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\PermissionManager;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\ResponseGenerator;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\VoiceCommandParser;

class TwilioConversationService
{
    protected ?ChatbotConversation $conversation = null;

    protected ?ChatbotHistory $history = null;

    protected ?Chatbot $chatbot = null;

    protected string $humanAgentCommand = 'humanagent';

    protected int $chatbotId;

    protected int $channelId;

    protected ?string $ipAddress = null;

    protected ?array $payload = null;

    protected bool $existMessage = false;

    /**
     * Handle incoming WhatsApp message
     */
    public function handleWhatsapp(): void
    {
        $twilio = app(TwilioWhatsappService::class)
            ->setChatbotChannel(ChatbotChannel::find($this->channelId));

        $waId = '+' . data_get($this->payload, 'WaId');
        $messageType = data_get($this->payload, 'MessageType');
        $messageBody = data_get($this->payload, 'Body');

        $conversation = $this->conversation;
        $chatbot = $conversation->chatbot;

        if ($conversation->connect_agent_at) {
            if ($conversation->last_activity_at->diffInMinutes() > 10) {
                $this->closeInactiveConversation($conversation, $twilio, $waId);

                return;
            }

            return;
        }

        $conversation->update(['last_activity_at' => now()]);

        if ($messageType === 'text' && is_string($messageBody)) {
            $this->processTextMessage($messageBody, $conversation, $chatbot, $twilio, $waId, 'whatsapp');
        } else {
            $this->sendUnsupportedMessageType($conversation, $chatbot, $twilio, $waId, 'whatsapp');
        }
    }

    /**
     * Handle incoming SMS message
     */
    public function handleSms(): void
    {
        $twilio = app(TwilioSmsService::class)
            ->setChatbotChannel(ChatbotChannel::find($this->channelId));

        $phoneNumber = data_get($this->payload, 'From');
        $messageBody = data_get($this->payload, 'Body');

        $conversation = $this->conversation;
        $chatbot = $conversation->chatbot;

        if ($conversation->connect_agent_at) {
            if ($conversation->last_activity_at->diffInMinutes() > 10) {
                $this->closeInactiveConversation($conversation, $twilio, $phoneNumber);

                return;
            }

            return;
        }

        $conversation->update(['last_activity_at' => now()]);

        if (is_string($messageBody)) {
            $this->processTextMessage($messageBody, $conversation, $chatbot, $twilio, $phoneNumber, 'sms');
        } else {
            $this->sendUnsupportedMessageType($conversation, $chatbot, $twilio, $phoneNumber, 'sms');
        }
    }

    /**
     * Handle incoming voice call
     * Returns TwiML XML response
     */
    public function handleVoice(): string
    {
        $twilio = app(TwilioVoiceService::class)
            ->setChatbotChannel(ChatbotChannel::find($this->channelId));

        $conversation = $this->conversation;
        $chatbot = $conversation->chatbot;
        $callSid = data_get($this->payload, 'CallSid');

        // Update conversation with voice call info
        $conversation->update([
            'chatbot_channel'    => 'voice',
            'call_phone_number'  => data_get($this->payload, 'From'),
            'call_status'        => 'connected',
            'call_started_at'    => now(),
            'last_activity_at'   => now(),
        ]);

        // Record call SID for tracking
        $this->insertMessage(
            conversation: $conversation,
            message: '[CALL INITIATED] Call SID: ' . $callSid,
            role: 'system',
            model: $chatbot->ai_model,
            messageType: 'voice_call_started',
            isInternalNote: true
        );

        // Return TwiML for voice flow with welcome message
        $greeting = $chatbot->voice_call_first_message 
            ?? $chatbot->welcome_message 
            ?? 'Hello, how can I help you today?';

        return $twilio->handleIncomingCall(
            $greeting,
            [
                'voice'    => 'alice',
                'language' => $chatbot->language ?? 'en-US',
            ]
        );
    }

    /**
     * Process voice transcript from incoming speech
     */
    public function processVoiceTranscript(string $transcript, ChatbotConversation $conversation): string
    {
        $chatbot = $conversation->chatbot;
        $twilio = app(TwilioVoiceService::class)
            ->setChatbotChannel(ChatbotChannel::find($this->channelId));

        // Store user's spoken message
        $this->insertMessage(
            conversation: $conversation,
            message: $transcript,
            role: 'user',
            model: $chatbot->ai_model,
            messageType: 'voice_transcript_user'
        );

        $conversation->update(['last_activity_at' => now()]);

        // Check if human agent is needed
        if ($conversation->connect_agent_at) {
            return $twilio->hangup(trans('Connecting you to a support agent. Please hold.'));
        }

        // Resolve as voice command first, then fall back to AI
        $response = $this->resolveVoiceCommand($transcript, $conversation)
            ?? $this->generateResponse($transcript)
            ?? trans("Sorry, I can't answer right now.");

        // Store AI response
        $this->insertMessage(
            conversation: $conversation,
            message: $response,
            role: 'assistant',
            model: $chatbot->ai_model,
            messageType: 'voice_transcript_assistant'
        );

        // Check if should switch to human agent (SMART_SWITCH mode)
        if ($chatbot->interaction_type === InteractionType::SMART_SWITCH 
            && MarketplaceHelper::isRegistered('chatbot-agent')) {
            
            if ($this->shouldSwitchToAgent($response, $chatbot)) {
                $conversation->update(['connect_agent_at' => now()]);
                return $twilio->hangup(trans('Connecting you to a support agent. Please hold.'));
            }
        }

        // Return TwiML to play response and listen for next input
        return $twilio->playResponse(
            $response,
            [
                'voice'    => 'alice',
                'language' => $chatbot->language ?? 'en-US',
                'action'   => route('api.v2.chatbot.voice.transcript', [
                    'chatbot'      => $chatbot->uuid,
                    'conversation' => $conversation->id,
                    'channelId'    => $this->channelId,
                ]),
            ]
        );
    }

    /**
     * Determine if should switch to human agent
     */
    protected function shouldSwitchToAgent(string $response, Chatbot $chatbot): bool
    {
        // Check for explicit agent request marker
        if (str_contains($response, '[human-agent-direct]')) {
            return true;
        }

        // Check human agent conditions if set
        if (!empty($chatbot->human_agent_conditions)) {
            $conditions = is_array($chatbot->human_agent_conditions) 
                ? $chatbot->human_agent_conditions 
                : json_decode($chatbot->human_agent_conditions, true);

            foreach ($conditions as $condition) {
                if (str_contains(strtolower($response), strtolower($condition))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Process text message (works for SMS, WhatsApp, etc.)
     */
    protected function processTextMessage(
        string $messageBody,
        ChatbotConversation $conversation,
        Chatbot $chatbot,
        object $twilioService,
        string $phoneNumber,
        string $channel = 'sms'
    ): void
    {
        if ($this->isHumanAgentCommand($chatbot, $messageBody)) {
            $this->connectToHumanAgent($chatbot, $conversation, $twilioService, $phoneNumber);
            return;
        }

        $response = $this->generateResponse($messageBody) 
            ?? trans("Sorry, I can't answer right now.");

        if (!$conversation->connect_agent_at 
            && $chatbot->interaction_type === InteractionType::SMART_SWITCH 
            && MarketplaceHelper::isRegistered('chatbot-agent')) {
            
            $response .= "\n\n" . trans("To speak with a live support agent, reply with: #{$this->humanAgentCommand}");
        }

        $twilioService->sendText($response, $phoneNumber);
        
        $this->insertMessage(
            conversation: $conversation,
            message: $response,
            role: 'assistant',
            model: $chatbot->ai_model
        );
    }

    /**
     * Send unsupported message type response
     */
    protected function sendUnsupportedMessageType(
        ChatbotConversation $conversation,
        Chatbot $chatbot,
        object $twilioService,
        string $phoneNumber,
        string $channel = 'sms'
    ): void
    {
        $message = trans('The chatbot does not support the type of message you are sending.');
        
        $this->insertMessage(
            conversation: $conversation,
            message: $message,
            role: 'assistant',
            model: $chatbot->ai_model
        );

        $twilioService->sendText($message, $phoneNumber);
    }

    /**
     * Close inactive conversation
     */
    protected function closeInactiveConversation(
        ChatbotConversation $conversation,
        object $twilioService,
        string $phoneNumber
    ): void
    {
        $conversation->update(['connect_agent_at' => null]);
        $message = trans('The conversation has been closed due to inactivity.');
        
        $this->insertMessage(
            conversation: $conversation,
            message: $message,
            role: 'assistant',
            model: $conversation->chatbot->ai_model
        );

        $twilioService->sendText($message, $phoneNumber);
    }

    /**
     * Check if message is human agent command
     */
    protected function isHumanAgentCommand(Chatbot $chatbot, string $message): bool
    {
        return str_contains(strtolower($message), strtolower($this->humanAgentCommand)) 
            && $chatbot->interaction_type === InteractionType::SMART_SWITCH;
    }

    /**
     * Connect to human agent
     */
    protected function connectToHumanAgent(
        Chatbot $chatbot,
        ChatbotConversation $conversation,
        object $twilioService,
        string $phoneNumber
    ): void
    {
        $conversation->update(['connect_agent_at' => now()]);

        if ($connectMessage = $chatbot->connect_message) {
            $chatbotHistory = $this->insertMessage(
                conversation: $conversation,
                message: $connectMessage,
                role: 'assistant',
                model: $chatbot->ai_model,
                forcePanelEvent: true
            );

            $twilioService->sendText($connectMessage, $phoneNumber);
            $this->dispatchAgentEvent($chatbot, $conversation, $chatbotHistory);
        }
    }


    protected function resolveVoiceCommand(string $transcript, ChatbotConversation $conversation): ?string
    {
        if (!config('unified-communication.voice_commands.enabled', true)) {
            return null;
        }

        $parser = app(VoiceCommandParser::class);
        $permissions = app(PermissionManager::class);
        $router = app(IntentRouter::class);
        $responses = app(ResponseGenerator::class);
        $context = app(ContextManager::class);

        if (in_array(trim(mb_strtolower($transcript)), ['yes', 'confirm', 'confirmed'], true)) {
            $pending = $context->getPending($conversation);
            if (is_array($pending) && isset($pending['intent'])) {
                $command = $parser->parse((string) ($pending['transcript'] ?? $transcript));
                $result = $router->route($command, $conversation);
                $context->clearPending($conversation);
                $this->logVoiceCommand($conversation, $command->originalTranscript, $command->intent, $command->entities, $command->confidence, 'executed');
                return $responses->completed($result);
            }
        }

        $command = $parser->parse($transcript);
        $this->logVoiceCommand($conversation, $command->originalTranscript, $command->intent, $command->entities, $command->confidence, 'parsed');

        if ($command->intent === 'unknown') {
            return null;
        }

        if ($command->isIncomplete()) {
            return $responses->missingInformation($command);
        }

        if (!$permissions->canExecute($command, $conversation)) {
            return $responses->permissionDenied();
        }

        $executeThreshold = (float) config('unified-communication.voice_commands.execute_threshold', 0.90);
        $confirmThreshold = (float) config('unified-communication.voice_commands.confirmation_threshold', 0.60);

        if ($command->shouldExecuteImmediately($executeThreshold)) {
            $result = $router->route($command, $conversation);
            $this->logVoiceCommand($conversation, $command->originalTranscript, $command->intent, $command->entities, $command->confidence, 'executed');
            return $responses->completed($result);
        }

        if ($command->requiresConfirmation($confirmThreshold)) {
            $context->rememberPending($conversation, [
                'intent' => $command->intent,
                'transcript' => $command->originalTranscript,
                'entities' => $command->entities,
            ]);
            $this->logVoiceCommand($conversation, $command->originalTranscript, $command->intent, $command->entities, $command->confidence, 'awaiting_confirmation');
            return $responses->confirmation($command);
        }

        return null;
    }

    protected function logVoiceCommand(
        ChatbotConversation $conversation,
        string $transcript,
        string $intent,
        array $entities,
        float $confidence,
        string $status
    ): void {
        try {
            VoiceCommandLog::query()->create([
                'user_id' => auth()->id(),
                'conversation_id' => $conversation->getKey(),
                'transcript' => $transcript,
                'parsed_intent' => $intent,
                'entities' => $entities,
                'confidence' => $confidence,
                'status' => $status,
                'duration_ms' => null,
            ]);
        } catch (Exception $e) {
            Log::warning('Unable to write voice command log: ' . $e->getMessage());
        }
    }

    /**
     * Generate response using AI
     */
    protected function generateResponse(string $prompt): ?string
    {
        try {
            return app(GeneratorService::class)
                ->setChatbot($this->conversation->chatbot)
                ->setConversation($this->conversation)
                ->setPrompt($prompt)
                ->generate();
        } catch (Exception $e) {
            Log::error('Chatbot generation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Dispatch event to agent panel
     */
    protected function dispatchAgentEvent(
        Chatbot $chatbot,
        ChatbotConversation $conversation,
        ?ChatbotHistory $chatbotHistory
    ): void
    {
        if (MarketplaceHelper::isRegistered('chatbot-agent')) {
            try {
                ChatbotForPanelEventAbly::dispatch(
                    $chatbot,
                    $conversation->load('lastMessage'),
                    $chatbotHistory
                );
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    /**
     * Insert message into conversation
     */
    public function insertMessage(
        ChatbotConversation $conversation,
        string $message,
        string $role,
        string $model,
        bool $forcePanelEvent = false,
        string $messageType = 'text',
        bool $isInternalNote = false,
        ?string $mediaUrl = null,
        ?string $mediaName = null,
    ): ?ChatbotHistory
    {
        try {
            $chatbotHistory = ChatbotHistory::query()->create([
                'chatbot_id'      => $conversation->getAttribute('chatbot_id'),
                'conversation_id' => $conversation->getAttribute('id'),
                'message_id'      => data_get($this->payload, 'SmsSid') ?? data_get($this->payload, 'CallSid'),
                'role'            => $role,
                'model'           => $model,
                'message'         => $message,
                'message_type'    => $messageType,
                'media_url'       => $mediaUrl,
                'media_name'      => $mediaName,
                'is_internal_note' => $isInternalNote,
                'created_at'      => now(),
            ]);

            if ($forcePanelEvent) {
                $this->dispatchAgentEvent($conversation->chatbot, $conversation, $chatbotHistory);
            }

            return $chatbotHistory;
        } catch (Exception $e) {
            Log::error('Error inserting message: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Store conversation
     */
    public function storeConversation(): ChatbotConversation
    {
        $query = ChatbotConversation::query()
            ->where('chatbot_id', $this->chatbotId)
            ->where('chatbot_channel', $this->detectChannel())
            ->where('session_id', data_get($this->payload, 'From') ?? data_get($this->payload, 'WaId'))
            ->where('is_showed_on_history', false);

        if ($query->exists()) {
            $this->existMessage = true;
            return $query->first();
        }

        return ChatbotConversation::query()->create([
            'chatbot_channel'    => $this->detectChannel(),
            'is_showed_on_history' => false,
            'country_code'       => Helper::getRequestCountryCode(),
            'ip_address'         => $this->ipAddress,
            'conversation_name'  => $this->extractConversationName(),
            'chatbot_id'         => $this->chatbotId,
            'session_id'         => data_get($this->payload, 'From') ?? data_get($this->payload, 'WaId'),
            'last_activity_at'   => now(),
        ]);
    }

    /**
     * Detect channel type from payload
     */
    protected function detectChannel(): string
    {
        if (data_get($this->payload, 'WaId')) {
            return 'whatsapp';
        }

        if (data_get($this->payload, 'CallSid')) {
            return 'voice';
        }

        if (data_get($this->payload, 'SmsSid')) {
            return 'sms';
        }

        return 'unknown';
    }

    /**
     * Extract conversation name from payload
     */
    protected function extractConversationName(): string
    {
        $channel = $this->detectChannel();

        return match ($channel) {
            'whatsapp' => 'WhatsApp User',
            'sms'      => 'SMS User',
            'voice'    => 'Voice Call - ' . data_get($this->payload, 'From', 'Unknown'),
            default    => 'Anonymous User',
        };
    }

    /**
     * Get conversation
     */
    public function getConversation(): ?ChatbotConversation
    {
        return $this->conversation;
    }

    /**
     * Set conversation
     */
    public function setConversation(ChatbotConversation $conversation): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    /**
     * Get chatbot
     */
    public function getChatbot(): ?Chatbot
    {
        return $this->chatbot;
    }

    /**
     * Set chatbot
     */
    public function setChatbot(Chatbot $chatbot): self
    {
        $this->chatbot = $chatbot;

        return $this;
    }

    /**
     * Set IP address
     */
    public function setIpAddress(): self
    {
        $this->ipAddress = Helper::getRequestIp();

        return $this;
    }

    /**
     * Set chatbot ID
     */
    public function setChatbotId(int $chatbotId): self
    {
        $this->chatbotId = $chatbotId;
        $this->chatbot = Chatbot::find($chatbotId);

        return $this;
    }

    /**
     * Set channel ID
     */
    public function setChannelId(int $channelId): self
    {
        $this->channelId = $channelId;

        return $this;
    }

    /**
     * Set payload
     */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Check if message already exists
     */
    public function existMessage(): bool
    {
        return $this->existMessage;
    }
}
