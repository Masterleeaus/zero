<?php

namespace App\Extensions\Chatbot\App\Services\Channels;

class ChannelBridgeRegistry
{
    public function all(): array
    {
        return config('chatbot.channel_bridges', []);
    }

    public function get(string $channel): array
    {
        return $this->all()[$channel] ?? [];
    }

    public function enabled(string $channel): bool
    {
        return (bool) ($this->get($channel)['enabled'] ?? false);
    }
}
