<?php

namespace App\Extensions\Chatbot\App\Services\Ingestion;

use App\Extensions\Chatbot\App\Services\Channels\ChannelBridgeDispatcher;
use App\Services\Omni\OmniAdapterRegistry;
use App\Services\Omni\OmniDualWriteManager;

class ConnectedExtensionIngressService
{
    public function __construct(
        protected ChannelBridgeDispatcher $dispatcher,
        protected OmniAdapterRegistry $omniAdapters,
        protected OmniDualWriteManager $omniDualWriteManager,
    ) {
    }

    public function ingest(string $channel, array $payload): array
    {
        $bridge = $this->dispatcher->dispatchInbound($channel, $payload);

        $adapter = $this->omniAdapters->for($channel);
        $normalized = $adapter->mirrorToOmni($payload);

        $normalized['company_id'] = $normalized['company_id'] ?? data_get($payload, 'company_id');
        $normalized['agent_id'] = $normalized['agent_id'] ?? data_get($payload, 'agent_id');
        $normalized['message_metadata'] = array_merge($normalized['message_metadata'] ?? [], [
            'bridge' => $bridge,
            'ingress_channel' => $channel,
        ]);

        $omni = null;
        if (! empty($normalized['company_id']) && ! empty($normalized['agent_id'])) {
            $omni = $this->omniDualWriteManager->ingest($normalized);
        }

        return [
            'bridge' => $bridge,
            'external_user_id' => data_get($payload, 'external_user_id'),
            'text' => data_get($payload, 'text'),
            'attachments' => data_get($payload, 'attachments', []),
            'status' => 'accepted',
            'omni_conversation_id' => $omni['conversation']->id ?? null,
            'omni_message_id' => $omni['message']->id ?? null,
        ];
    }
}
