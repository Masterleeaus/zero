<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\ChatbotWhatsapp\System\Models\OfflineVoiceAction;

class OfflineSyncQueueService
{
    public function queue(int $conversationId, string $channel, string $transcript, array $payload = []): OfflineVoiceAction
    {
        return OfflineVoiceAction::query()->create([
            'conversation_id' => $conversationId,
            'channel' => $channel,
            'transcript' => $transcript,
            'payload' => $payload,
            'status' => 'pending',
        ]);
    }
}
