<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification across different channels.
     */
    public function send($userId, $type, $recipient, $content)
    {
        // Internal logic: Dispatch to appropriate driver (mocked for now)
        Log::info("Dispatching {$type} to {$recipient}: {$content}");

        return NotificationLog::create([
            'user_id' => $userId,
            'type' => $type,
            'recipient' => $recipient,
            'content' => $content,
            'status' => 'sent'
        ]);
    }

    /**
     * Bulk message dispatch for marketing.
     */
    public function bulkSend($recipients, $type, $message)
    {
        $count = 0;
        foreach ($recipients as $recipient) {
            $this->send($recipient['user_id'] ?? null, $type, $recipient['address'], $message);
            $count++;
        }
        return $count;
    }
}
