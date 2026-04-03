<?php

namespace App\Titan\Signals\Subscribers;

use App\Titan\Signals\SignalSubscriberInterface;

class ZeroSubscriber implements SignalSubscriberInterface
{
    public function name(): string
    {
        return 'zero';
    }

    public function handle(array $signal): array
    {
        return [
            'accepted' => true,
            'envelope_hint' => [
                'company_id' => $signal['company_id'] ?? null,
                'team_id' => $signal['team_id'] ?? null,
                'actor_id' => $signal['user_id'] ?? null,
                'priority' => $signal['severity'] ?? 'AMBER',
            ],
        ];
    }
}
