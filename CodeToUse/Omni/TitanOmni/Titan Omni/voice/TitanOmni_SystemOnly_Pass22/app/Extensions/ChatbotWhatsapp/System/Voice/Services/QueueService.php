<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

class QueueService
{
    public function queueLength(): int
    {
        return (int) config('unified-communication.queue.mock_queue_length', 0);
    }

    public function availableAgents(): int
    {
        return max(0, (int) config('unified-communication.queue.mock_available_agents', 1));
    }

    public function estimatedWaitMinutes(): int
    {
        $queueLength = $this->queueLength();
        $availableAgents = max(1, $this->availableAgents());
        $avgDuration = max(1, (int) config('unified-communication.queue.average_duration_minutes', 5));

        return (int) ceil(($queueLength * $avgDuration) / $availableAgents);
    }

    public function shouldOfferCallback(): bool
    {
        return ($this->estimatedWaitMinutes() * 60) >= (int) config('unified-communication.queue.max_wait_offer_callback', 300);
    }
}
