<?php

namespace App\Events\TitanCore;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcasts a real-time Titan Core activity event to the admin feed.
 *
 * Channel: titan.core.activity
 */
class TitanActivityEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @param  array<string, mixed>  $payload */
    public function __construct(public readonly array $payload)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('titan.core.activity');
    }

    public function broadcastAs(): string
    {
        return 'titan.activity';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'intent'     => $this->payload['intent'] ?? null,
            'provider'   => $this->payload['provider'] ?? null,
            'duration'   => $this->payload['duration'] ?? null,
            'tokens'     => $this->payload['tokens'] ?? null,
            'company_id' => $this->payload['company_id'] ?? null,
            'user_id'    => $this->payload['user_id'] ?? null,
            'status'     => $this->payload['status'] ?? 'unknown',
            'event_type' => $this->payload['event_type'] ?? 'ai.request',
            'timestamp'  => now()->toIso8601String(),
        ];
    }
}
