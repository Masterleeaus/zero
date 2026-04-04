<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Models\CallLog;
use App\Extensions\ChatbotWhatsapp\System\Services\Twillio\TwilioVoiceService;

class CallRouter
{
    public function __construct(
        protected BusinessHoursService $businessHours,
        protected IvrService $ivr,
        protected QueueService $queue,
        protected CallbackService $callbacks,
        protected VoicemailService $voicemail,
        protected CallTransferService $transfer,
    ) {}

    public function routeIncomingCall(ChatbotConversation $conversation, array $payload, TwilioVoiceService $voiceService, int $channelId): string
    {
        $chatbot = $conversation->chatbot;
        $callSid = (string) data_get($payload, 'CallSid', '');
        $from = (string) data_get($payload, 'From', '');

        CallLog::query()->create([
            'conversation_id' => $conversation->id,
            'call_sid' => $callSid,
            'from_number' => $from,
            'to_number' => (string) data_get($payload, 'To', ''),
            'call_status' => 'incoming',
            'metadata' => ['channel_id' => $channelId],
        ]);

        if (!$this->businessHours->isOpen()) {
            $next = $this->businessHours->nextOpening()->format('l g:i A');
            $action = route('api.v2.chatbot.voice.menu', [
                'chatbot' => $chatbot->uuid,
                'conversation' => $conversation->id,
                'channelId' => $channelId,
            ]);

            return $this->ivr->presentMenu([
                'message' => "We're currently closed. Our next opening time is {$next}. Press 1 to leave a voicemail or 2 to request a callback.",
            ], $action);
        }

        if ($this->queue->shouldOfferCallback()) {
            $action = route('api.v2.chatbot.voice.menu', [
                'chatbot' => $chatbot->uuid,
                'conversation' => $conversation->id,
                'channelId' => $channelId,
            ]);

            return $this->ivr->queueOrCallbackMenu($action, $this->queue->estimatedWaitMinutes());
        }

        return $voiceService->handleIncomingCall(
            $chatbot->voice_call_first_message
                ?? $chatbot->welcome_message
                ?? 'Hello, how can I help you today?',
            [
                'voice' => 'alice',
                'language' => $chatbot->language ?? 'en-US',
                'action' => route('api.v2.chatbot.voice.transcript', [
                    'chatbot' => $chatbot->uuid,
                    'conversation' => $conversation->id,
                    'channelId' => $channelId,
                ]),
            ]
        );
    }
}
