<?php

namespace App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Services\Twillio\TwilioConversationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoiceCallController extends Controller
{
    public function __construct(
        public TwilioConversationService $service
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

            // Process the transcript and get response
            $twiml = $this->service->processVoiceTranscript($transcript, $chatbotConversation);

            // Log confidence score
            Log::info('Voice transcript processed', [
                'chatbot_id'       => $chatbot->id,
                'conversation_id'  => $conversation,
                'transcript'       => $transcript,
                'confidence'       => $confidence,
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
