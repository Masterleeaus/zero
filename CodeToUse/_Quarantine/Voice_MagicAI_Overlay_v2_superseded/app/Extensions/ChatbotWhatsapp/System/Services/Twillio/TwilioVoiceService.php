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
    public function handleIncomingCall(string $greeting, array $options = []): string
    {
        $response = new VoiceResponse();

        $voice = data_get($options, 'voice', 'alice');
        $language = data_get($options, 'language', 'en-US');

        // Play greeting message as audio
        $response->say($greeting, [
            'voice'    => $voice,
            'language' => $language,
        ]);

        // Gather speech input (user speaking)
        $gather = $response->gather([
            'action'           => data_get($options, 'action', ''),
            'method'           => 'POST',
            'speechTimeout'    => 'auto',
            'speechModel'      => 'default',
            'language'         => $language,
            'maxSpeechTime'    => 60,
            'numDigits'        => 1,
        ]);

        // Play prompt to start speaking
        $gather->say('Please leave your message after the beep.', [
            'voice'    => $voice,
            'language' => $language,
        ]);

        return $response->asXml();
    }

    /**
     * Send voice response to caller
     */
    public function playResponse(
        string $responseText,
        array $options = []
    ): string
    {
        $response = new VoiceResponse();

        $voice = data_get($options, 'voice', 'alice');
        $language = data_get($options, 'language', 'en-US');

        // Play AI response as audio
        $response->say($responseText, [
            'voice'    => $voice,
            'language' => $language,
        ]);

        // Ask if they want to continue or hang up
        $gather = $response->gather([
            'action'           => data_get($options, 'action', ''),
            'method'           => 'POST',
            'speechTimeout'    => 'auto',
            'speechModel'      => 'default',
            'language'         => $language,
            'maxSpeechTime'    => 60,
        ]);

        $gather->say('Say something or press any key to continue. Hang up to exit.', [
            'voice'    => $voice,
            'language' => $language,
        ]);

        return $response->asXml();
    }

    /**
     * End call gracefully
     */
    public function hangup(string $message = ''): string
    {
        $response = new VoiceResponse();

        if (!empty($message)) {
            $response->say($message, [
                'voice'    => 'alice',
                'language' => 'en-US',
            ]);
        }

        $response->hangup();

        return $response->asXml();
    }

    /**
     * Create callback URL for voice call
     */
    public function createCallbackUrl(
        string $chatbotUuid,
        string $conversationId,
        int $channelId,
        string $action = 'transcript'
    ): string
    {
        return route('api.v2.chatbot.voice.' . $action, [
            'chatbot'      => $chatbotUuid,
            'conversation' => $conversationId,
            'channelId'    => $channelId,
        ]);
    }

    /**
     * Make outbound voice call
     */
    public function makeCall(
        string $toNumber,
        string $greeting,
        string $callbackUrl,
        array $options = []
    ): array
    {
        try {
            $client = $this->client();
            $from = data_get($this->chatbotChannel['credentials'], 'voice_phone');

            $twiml = $this->handleIncomingCall($greeting, array_merge($options, [
                'action' => $callbackUrl,
            ]));

            $call = $client->calls->create(
                $this->formatPhoneNumber($toNumber),
                $from,
                [
                    'twiml'           => $twiml,
                    'statusCallback'  => data_get($options, 'statusCallback', ''),
                    'statusCallbackMethod' => 'POST',
                ]
            );

            return [
                'status'    => true,
                'call_sid'  => $call->sid,
                'message'   => trans('Voice call initiated'),
                'from'      => $call->from,
                'to'        => $call->to,
                'status_cb' => $call->status,
            ];
        } catch (Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Record call (if enabled)
     */
    public function recordCall(string $callSid): array
    {
        try {
            $client = $this->client();

            $recording = $client->calls($callSid)->recordings->stream();

            return [
                'status'     => true,
                'recordings' => $recording,
            ];
        } catch (Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get call details
     */
    public function getCallDetails(string $callSid): array
    {
        try {
            $client = $this->client();
            $call = $client->calls($callSid)->fetch();

            return [
                'status'         => true,
                'call_sid'       => $call->sid,
                'from'           => $call->from,
                'to'             => $call->to,
                'duration'       => $call->duration,
                'call_status'    => $call->status,
                'start_time'     => $call->startTime,
                'end_time'       => $call->endTime,
                'price'          => $call->price,
                'price_unit'     => $call->priceUnit,
                'direction'      => $call->direction,
            ];
        } catch (Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Transfer call to another number
     */
    public function transferCall(string $callSid, string $transferTo, string $callbackUrl): array
    {
        try {
            $client = $this->client();
            $from = data_get($this->chatbotChannel['credentials'], 'voice_phone');

            // Create TwiML for transfer
            $twiml = new VoiceResponse();
            $dial = $twiml->dial();
            $dial->number($transferTo);

            $client->calls($callSid)->update([
                'twiml' => $twiml->asXml(),
            ]);

            return [
                'status'  => true,
                'message' => trans('Call transferred'),
            ];
        } catch (Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number for Twilio
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // Ensure + prefix
        if (!str_starts_with($cleaned, '+')) {
            $cleaned = '+' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Get Twilio client
     */
    public function client(): Client
    {
        $username = data_get($this->chatbotChannel['credentials'], 'voice_sid');
        $password = data_get($this->chatbotChannel['credentials'], 'voice_token');

        return new Client($username, $password);
    }

    /**
     * Set chatbot channel
     */
    public function setChatbotChannel(ChatbotChannel $chatbotChannel): self
    {
        $this->chatbotChannel = $chatbotChannel;

        return $this;
    }

    /**
     * Get chatbot channel
     */
    public function getChatbotChannel(): ChatbotChannel
    {
        return $this->chatbotChannel;
    }
}
