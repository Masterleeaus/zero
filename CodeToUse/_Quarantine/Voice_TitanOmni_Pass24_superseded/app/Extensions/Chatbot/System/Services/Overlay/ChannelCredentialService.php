<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Services\Overlay;

use App\Extensions\Chatbot\System\Enums\ChannelTypeEnum;
use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotChannel;
use App\Extensions\Chatbot\System\Support\WorkspaceResolver;

class ChannelCredentialService
{
    public function __construct(protected WorkspaceResolver $workspaceResolver)
    {
    }

    public function upsert(Chatbot $chatbot, string $channel, array $credentials = [], array $payload = []): ChatbotChannel
    {
        $tenant = $this->workspaceResolver->resolveForChatbot($chatbot);
        $channelType = ChannelTypeEnum::tryFrom($channel)?->value ?? ChannelTypeEnum::Generic->value;

        return ChatbotChannel::query()->updateOrCreate(
            [
                'chatbot_id' => $chatbot->id,
                'channel' => $channelType,
            ],
            [
                'user_id' => $chatbot->user_id,
                'credentials' => $credentials,
                'payload' => array_merge($payload, ['configured_via' => 'overlay-command']),
                'connected_at' => now(),
                'team_id' => $tenant['team_id'],
                'company_id' => $tenant['company_id'],
            ]
        );
    }
}
