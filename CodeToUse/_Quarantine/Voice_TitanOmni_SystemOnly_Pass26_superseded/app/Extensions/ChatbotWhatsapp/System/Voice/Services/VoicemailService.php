<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use Twilio\TwiML\VoiceResponse;

class VoicemailService
{
    public function recordPrompt(string $actionUrl): string
    {
        $response = new VoiceResponse();
        $response->say('Please leave your voicemail after the beep. Press any key when finished.', [
            'voice' => 'alice',
            'language' => 'en-US',
        ]);

        $response->record([
            'action' => $actionUrl,
            'method' => 'POST',
            'maxLength' => (int) config('unified-communication.voicemail.max_duration', 600),
            'playBeep' => true,
            'transcribe' => (bool) config('unified-communication.voicemail.transcribe', true),
        ]);

        $response->say('No recording received. Goodbye.');
        $response->hangup();

        return $response->asXml();
    }

    public function completedResponse(): string
    {
        $response = new VoiceResponse();
        $response->say('Thank you. Your voicemail has been saved. Goodbye.', [
            'voice' => 'alice',
            'language' => 'en-US',
        ]);
        $response->hangup();

        return $response->asXml();
    }
}
