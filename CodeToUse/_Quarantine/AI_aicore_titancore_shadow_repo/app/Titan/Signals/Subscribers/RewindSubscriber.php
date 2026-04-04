<?php

namespace App\Titan\Signals\Subscribers;

use App\Titan\Signals\SignalSubscriberInterface;

class RewindSubscriber implements SignalSubscriberInterface
{
    public function name(): string
    {
        return 'rewind';
    }

    public function handle(array $signal): array
    {
        return [
            'accepted' => true,
            'anchor' => [
                'process_id' => $signal['process_id'] ?? null,
                'signal_id' => $signal['id'] ?? null,
                'rewind_from' => $signal['rewind_from'] ?? null,
            ],
        ];
    }
}
