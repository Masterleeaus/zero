<?php

namespace App\Extensions\Chatbot\App\Services\Ingestion;

use App\Extensions\Chatbot\App\Services\Channels\ChannelBridgeDispatcher;

class ConnectedExtensionIngressService
{
    public function __construct(protected ChannelBridgeDispatcher $dispatcher)
    {
    }

    public function ingest(string $channel, array $payload): array
    {
        $bridge = $this->dispatcher->dispatchInbound($channel, $payload);

        return [
            'bridge' => $bridge,
            'external_user_id' => data_get($payload, 'external_user_id'),
            'text' => data_get($payload, 'text'),
            'attachments' => data_get($payload, 'attachments', []),
            'status' => 'accepted',
        ];
    }
}
