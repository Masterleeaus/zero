<?php

namespace App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook;

use App\Extensions\Chatbot\System\Models\ChatbotChannelWebhook;
use App\Extensions\ChatbotWhatsapp\System\Services\Twillio\TwilioConversationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotTwilioController extends Controller
{
    public function __construct(
        public TwilioConversationService $service
    ) {}

    /**
     * Handle inbound Twilio webhook
     * Routes to appropriate handler based on message type
     */
    public function handle(
        int $chatbotId,
        int $channelId,
        Request $request
    )
    {
        try {
            // Determine channel type from payload
            $channelType = $this->detectChannelType($request);

            // Validate that we have necessary data
            if (!$this->isValidRequest($request, $channelType)) {
                Log::warning('Invalid Twilio webhook', [
                    'chatbot_id'    => $chatbotId,
                    'channel_id'    => $channelId,
                    'channel_type'  => $channelType,
                ]);

                return response()->json(['status' => false], 400);
            }

            // Store webhook for audit trail
            ChatbotChannelWebhook::query()->create([
                'chatbot_id'         => $chatbotId,
                'chatbot_channel_id' => $channelId,
                'payload'            => $request->all(),
                'channel_type'       => $channelType,
                'created_at'         => now(),
            ]);

            // Initialize service
            $this->service
                ->setIpAddress()
                ->setChatbotId($chatbotId)
                ->setChannelId($channelId)
                ->setPayload($request->all());

            // Store or retrieve conversation
            $conversation = $this->service->storeConversation();
            $chatbot = $this->service->getChatbot();

            if (!$conversation || !$chatbot) {
                return response()->json(['status' => false], 404);
            }

            // Route based on channel type
            return match ($channelType) {
                'whatsapp' => $this->handleWhatsapp($request, $conversation, $chatbot),
                'sms'      => $this->handleSms($request, $conversation, $chatbot),
                'voice'    => $this->handleVoice($request, $conversation, $chatbot),
                default    => response()->json(['status' => false], 400),
            };
        } catch (\Exception $e) {
            Log::error('Twilio webhook error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle WhatsApp message
     */
    protected function handleWhatsapp($request, $conversation, $chatbot)
    {
        // Store user message
        $this->service->insertMessage(
            conversation: $conversation,
            message: $request->get('Body') ?? '',
            role: 'user',
            model: $chatbot->getAttribute('ai_model'),
            messageType: 'text'
        );

        // Process via conversation service
        $this->service->handleWhatsapp();

        return response()->json(['status' => true]);
    }

    /**
     * Handle SMS message
     */
    protected function handleSms($request, $conversation, $chatbot)
    {
        // Store user message
        $this->service->insertMessage(
            conversation: $conversation,
            message: $request->get('Body') ?? '',
            role: 'user',
            model: $chatbot->getAttribute('ai_model'),
            messageType: 'text'
        );

        // Process via conversation service
        $this->service->handleSms();

        return response()->json(['status' => true]);
    }

    /**
     * Handle voice call
     * Returns TwiML XML for Twilio
     */
    protected function handleVoice($request, $conversation, $chatbot)
    {
        // Generate and return TwiML for voice call
        $twiml = $this->service->handleVoice();

        return response($twiml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Detect channel type from webhook payload
     */
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

        // SMS has 'SmsSid' (Twilio's internal identifier)
        if ($request->has('SmsSid')) {
            return 'sms';
        }

        return 'unknown';
    }

    /**
     * Validate request based on channel type
     */
    protected function isValidRequest(Request $request, string $channelType): bool
    {
        return match ($channelType) {
            'whatsapp' => $request->has('WaId') && $request->has('Body'),
            'sms'      => $request->has('SmsSid') && $request->has('Body'),
            'voice'    => $request->has('CallSid'),
            default    => false,
        };
    }
}
