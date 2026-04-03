<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use Twilio\TwiML\VoiceResponse;

class IvrService
{
    public function presentMenu(array $menu, string $actionUrl): string
    {
        $response = new VoiceResponse();
        $gather = $response->gather([
            'action' => $actionUrl,
            'method' => 'POST',
            'numDigits' => 1,
            'timeout' => 5,
        ]);

        $gather->say((string) ($menu['message'] ?? 'Please choose an option.'), [
            'voice' => 'alice',
            'language' => 'en-US',
        ]);

        $response->redirect($actionUrl . '?timeout=1', ['method' => 'POST']);

        return $response->asXml();
    }

    public function queueOrCallbackMenu(string $actionUrl, int $waitMinutes): string
    {
        return $this->presentMenu([
            'message' => "All agents are busy. Estimated wait time is {$waitMinutes} minutes. Press 1 to hold, 2 to leave a voicemail, or 3 to request a callback.",
        ], $actionUrl);
    }
}
