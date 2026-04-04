<?php

namespace App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Models\CallLog;
use App\Extensions\ChatbotWhatsapp\System\Services\Twillio\TwilioConversationService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\CallbackService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\IvrService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\VoicemailService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\UnifiedCommandInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoiceCallController extends Controller
{
    public function __construct(
        public TwilioConversationService $service,
        public IvrService $ivrService,
        public VoicemailService $voicemailService,
        public CallbackService $callbackService,
        public UnifiedCommandInterface $unifiedCommandInterface,
    ) {}

    /**
     * Handle voice call transcript
     * Called when user finishes speaking
     */
    public function transcript(
        Request $request,
        Chatbot $chatbot,
        int $conversation,
        int $channelId
    )
    {
        try {
            // Find conversation
            $chatbotConversation = ChatbotConversation::findOrFail($conversation);

            // Get speech recognition result from Twilio
            $transcript = $request->input('SpeechResult');
            $confidence = $request->input('Confidence');

            if (!$transcript) {
                // No speech detected, prompt to try again
                return $this->getTwilioVoiceService($channelId)->playResponse(
                    trans('Sorry, I did not understand. Please try again.'),
                    [
                        'voice'    => 'alice',
                        'language' => $chatbot->language ?? 'en-US',
                    ]
                );
            }

            // Initialize service
            $this->service
                ->setIpAddress()
                ->setChatbotId($chatbot->id)
                ->setChannelId($channelId)
                ->setConversation($chatbotConversation);

            $result = $this->unifiedCommandInterface->handle($chatbotConversation, $transcript, 'voice');
            $twiml = $this->service->processVoiceTranscript($transcript, $chatbotConversation, $result->response);

            Log::info('Voice transcript processed', [
                'chatbot_id'       => $chatbot->id,
                'conversation_id'  => $conversation,
                'transcript'       => $transcript,
                'confidence'       => $confidence,
                'intent'           => $result->intent,
                'persona'          => $result->persona,
                'used_fallback'    => $result->usedFallback,
                'requires_confirmation' => $result->requiresConfirmation,
            ]);

            return response($twiml, 200, [
                'Content-Type' => 'application/xml',
            ]);
        } catch (\Exception $e) {
            Log::error('Voice transcript error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $voiceService = $this->getTwilioVoiceService($channelId);

            return response($voiceService->hangup(trans('An error occurred. Goodbye.')), 200, [
                'Content-Type' => 'application/xml',
            ]);
        }
    }


    public function menu(Request $request, Chatbot $chatbot, int $conversation, int $channelId)
    {
        $chatbotConversation = ChatbotConversation::findOrFail($conversation);
        $digits = (string) $request->input('Digits', '');

        if (in_array($digits, ['1', '2'], true)) {
            $action = $digits === '1'
                ? route('api.v2.chatbot.voice.voicemail', ['chatbot' => $chatbot->uuid, 'conversation' => $conversation, 'channelId' => $channelId])
                : route('api.v2.chatbot.voice.callback', ['chatbot' => $chatbot->uuid, 'conversation' => $conversation, 'channelId' => $channelId]);

            return response(
                $digits === '1'
                    ? $this->voicemailService->recordPrompt($action)
                    : $this->ivrService->presentMenu(['message' => 'Press 1 to confirm a callback in about one hour.'], $action),
                200,
                ['Content-Type' => 'application/xml']
            );
        }

        $xml = $this->getTwilioVoiceService($channelId)->hangup(trans('No valid option was selected. Goodbye.'));
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function voicemail(Request $request, Chatbot $chatbot, int $conversation, int $channelId)
    {
        $chatbotConversation = ChatbotConversation::findOrFail($conversation);
        CallLog::query()->where('conversation_id', $chatbotConversation->id)->latest('id')->first()?->update([
            'recording_url' => $request->input('RecordingUrl'),
            'call_status' => 'voicemail_recorded',
        ]);

        return response($this->voicemailService->completedResponse(), 200, ['Content-Type' => 'application/xml']);
    }

    public function callback(Request $request, Chatbot $chatbot, int $conversation, int $channelId)
    {
        $chatbotConversation = ChatbotConversation::findOrFail($conversation);
        $digits = (string) $request->input('Digits', '1');
        if ($digits !== '1') {
            return response($this->getTwilioVoiceService($channelId)->hangup(trans('Callback cancelled. Goodbye.')), 200, ['Content-Type' => 'application/xml']);
        }

        $schedule = $this->callbackService->schedule((string) $chatbotConversation->call_phone_number, null, [
            'conversation_id' => $chatbotConversation->id,
            'chatbot_id' => $chatbot->id,
        ]);

        $xml = $this->getTwilioVoiceService($channelId)->hangup(
            'Your callback has been scheduled for ' . $schedule->scheduled_at?->timezone(config('app.timezone'))->format('l g:i A') . '. Goodbye.'
        );

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Handle call status callback
     * Called when call state changes (ringing, answered, completed, etc.)
     */
    public function statusCallback(
        Request $request,
        Chatbot $chatbot,
        int $conversation,
        int $channelId
    )
    {
        try {
            $chatbotConversation = ChatbotConversation::findOrFail($conversation);
            $callStatus = $request->input('CallStatus');
            $callSid = $request->input('CallSid');
            $callDuration = (int) $request->input('CallDuration', 0);

            // Update conversation with call status
            $updates = [
                'call_status' => $callStatus,
            ];

            if ($callStatus === 'completed') {
                $updates['call_ended_at'] = now();
                $updates['call_duration_seconds'] = $callDuration;
            }

            $chatbotConversation->update($updates);

            CallLog::query()->updateOrCreate(
                ['call_sid' => $callSid],
                [
                    'conversation_id' => $chatbotConversation->id,
                    'from_number' => (string) $request->input('From'),
                    'to_number' => (string) $request->input('To'),
                    'call_status' => $callStatus,
                    'duration_seconds' => $callDuration,
                ]
            );

            // Log call status
            Log::info('Voice call status update', [
                'chatbot_id'      => $chatbot->id,
                'conversation_id' => $conversation,
                'call_sid'        => $callSid,
                'call_status'     => $callStatus,
                'duration'        => $callDuration,
            ]);

            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            Log::error('Voice status callback error: ' . $e->getMessage());

            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle call recording callback
     * Called when recording is available
     */
    public function recordingCallback(
        Request $request,
        Chatbot $chatbot,
        int $conversation,
        int $channelId
    )
    {
        try {
            $chatbotConversation = ChatbotConversation::findOrFail($conversation);
            $recordingSid = $request->input('RecordingSid');
            $recordingUrl = $request->input('RecordingUrl');
            $recordingDuration = (int) $request->input('RecordingDuration', 0);

            if (!$recordingSid) {
                return response()->json(['status' => false], 400);
            }

            // Store recording URL in conversation history or separate table
            // For now, we'll add it as an internal note
            $this->service
                ->setIpAddress()
                ->setChatbotId($chatbot->id)
                ->setChannelId($channelId)
                ->setConversation($chatbotConversation)
                ->setPayload($request->all());

            $this->service->insertMessage(
                conversation: $chatbotConversation,
                message: '[CALL RECORDING] Duration: ' . $recordingDuration . 's | URL: ' . $recordingUrl,
                role: 'system',
                model: $chatbot->ai_model,
                messageType: 'voice_call_recording',
                isInternalNote: true
            );

            Log::info('Call recording received', [
                'chatbot_id'       => $chatbot->id,
                'conversation_id'  => $conversation,
                'recording_sid'    => $recordingSid,
                'duration'         => $recordingDuration,
            ]);

            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            Log::error('Voice recording callback error: ' . $e->getMessage());

            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * End call gracefully
     */
    public function endCall(
        Request $request,
        Chatbot $chatbot,
        int $conversation,
        int $channelId
    )
    {
        try {
            $chatbotConversation = ChatbotConversation::findOrFail($conversation);

            // Update conversation
            $chatbotConversation->update([
                'call_status'    => 'ended',
                'call_ended_at'  => now(),
            ]);

            // Get voice service
            $voiceService = $this->getTwilioVoiceService($channelId);

            // Return hangup TwiML
            return response($voiceService->hangup(trans('Thank you for calling. Goodbye.')), 200, [
                'Content-Type' => 'application/xml',
            ]);
        } catch (\Exception $e) {
            Log::error('Voice end call error: ' . $e->getMessage());

            $voiceService = $this->getTwilioVoiceService($channelId);

            return response($voiceService->hangup(), 200, [
                'Content-Type' => 'application/xml',
            ]);
        }
    }

    /**
     * Helper to get Twilio voice service
     */
    protected function getTwilioVoiceService(int $channelId)
    {
        $voiceService = app(\App\Extensions\ChatbotWhatsapp\System\Services\Twillio\TwilioVoiceService::class);

        $channel = \App\Extensions\Chatbot\System\Models\ChatbotChannel::findOrFail($channelId);

        return $voiceService->setChatbotChannel($channel);
    }
}
