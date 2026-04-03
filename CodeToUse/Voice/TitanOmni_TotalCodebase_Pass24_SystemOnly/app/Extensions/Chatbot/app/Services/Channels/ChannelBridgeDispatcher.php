<?php

namespace App\Extensions\Chatbot\App\Services\Channels;

class ChannelBridgeDispatcher
{
    public function __construct(protected ChannelBridgeRegistry $registry)
    {
    }

    public function dispatchInbound(string $channel, array $payload): array
    {
        if (! $this->registry->enabled($channel)) {
            return ['handled' => false, 'reason' => 'disabled'];
        }

        return [
            'handled' => true,
            'channel' => $channel,
            'direction' => 'inbound',
            'payload' => $payload,
            'bridge' => $this->registry->get($channel),
        ];
    }

    public function dispatchOutbound(string $channel, array $payload): array
    {
        if (! $this->registry->enabled($channel)) {
            return ['handled' => false, 'reason' => 'disabled'];
        }

        return [
            'handled' => true,
            'channel' => $channel,
            'direction' => 'outbound',
            'payload' => $payload,
            'bridge' => $this->registry->get($channel),
        ];
    }
}
