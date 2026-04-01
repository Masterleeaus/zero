<?php

namespace App\Titan\Signals\Subscribers;

use App\Titan\Signals\SignalSubscriberInterface;

class PulseSubscriber implements SignalSubscriberInterface
{
    public function name(): string
    {
        return 'pulse';
    }

    public function handle(array $signal): array
    {
        $requiresApproval = (bool) data_get($signal, 'meta.requires_approval', false);

        return [
            'accepted' => true,
            'automation_state' => $requiresApproval ? 'awaiting-approval' : 'ready-for-automation',
            'rule_key' => $signal['type'] ?? 'generic',
        ];
    }
}
