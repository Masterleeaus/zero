<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Services\Channels;

use App\Extensions\Chatbot\System\Enums\ChannelTypeEnum;
use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotChannel;
use App\Extensions\Chatbot\System\Models\ChatbotChannelWebhook;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotCustomer;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use App\Extensions\Chatbot\System\Support\WorkspaceResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChannelWebhookService
{
    public function __construct(
        protected ChannelPayloadNormalizer $normalizer,
        protected WorkspaceResolver $workspaceResolver,
    ) {}

    public function ingest(Chatbot $chatbot, ChannelTypeEnum|string $channel, Request $request): array
    {
        $channel = $channel instanceof ChannelTypeEnum ? $channel : ChannelTypeEnum::from($channel);
        $normalized = $this->normalizer->normalize($channel, $request);
        $tenant = $this->workspaceResolver->resolveForChatbot($chatbot);

        return DB::transaction(function () use ($chatbot, $channel, $normalized, $tenant) {
            $channelModel = ChatbotChannel::query()->firstOrCreate(
                [
                    'chatbot_id' => $chatbot->id,
                    'channel' => $channel->value,
                ],
                [
                    'user_id' => $chatbot->user_id,
                    'payload' => ['source' => 'overlay-webhook'],
                    'credentials' => [],
                    'connected_at' => now(),
                    'team_id' => $tenant['team_id'],
                    'company_id' => $tenant['company_id'],
                ]
            );

            $customer = ChatbotCustomer::query()->firstOrCreate(
                [
                    'chatbot_id' => $chatbot->id,
                    'email' => null,
                    'phone' => $normalized['external_customer_id'] ?: null,
                ],
                [
                    'name' => $normalized['name'] ?: 'Channel User',
                    'user_id' => null,
                    'team_id' => $tenant['team_id'],
                    'company_id' => $tenant['company_id'],
                ]
            );

            $conversation = ChatbotConversation::query()->firstOrCreate(
                [
                    'chatbot_id' => $chatbot->id,
                    'chatbot_channel' => $channel->value,
                    'customer_channel_id' => $normalized['external_customer_id'] ?: Str::uuid()->toString(),
                    'session_id' => $normalized['external_customer_id'] ?: Str::uuid()->toString(),
                ],
                [
                    'chatbot_customer_id' => $customer->id,
                    'chatbot_channel_id' => $channelModel->id,
                    'conversation_name' => $normalized['name'] ?: ucfirst($channel->value) . ' conversation',
                    'customer_payload' => $normalized['raw'],
                    'last_activity_at' => now(),
                    'ticket_status' => 'open',
                    'closed' => false,
                    'team_id' => $tenant['team_id'],
                    'company_id' => $tenant['company_id'],
                    'workspace_id' => $tenant['workspace_id'],
                ]
            );

            $conversation->forceFill([
                'chatbot_customer_id' => $customer->id,
                'chatbot_channel_id' => $channelModel->id,
                'customer_payload' => $normalized['raw'],
                'last_activity_at' => now(),
                'closed' => false,
                'closed_at' => null,
                'team_id' => $tenant['team_id'],
                'company_id' => $tenant['company_id'],
                'workspace_id' => $tenant['workspace_id'],
            ])->save();

            $history = null;
            if ($normalized['message'] !== '') {
                $history = ChatbotHistory::query()->create([
                    'user_id' => null,
                    'chatbot_id' => $chatbot->id,
                    'conversation_id' => $conversation->id,
                    'message_id' => $normalized['external_message_id'] ?: (string) Str::uuid(),
                    'model' => $channel->value . '-webhook',
                    'role' => 'user',
                    'message' => $normalized['message'],
                    'message_type' => 'text',
                    'created_at' => now(),
                    'team_id' => $tenant['team_id'],
                    'company_id' => $tenant['company_id'],
                ]);
            }

            ChatbotChannelWebhook::query()->create([
                'chatbot_id' => $chatbot->id,
                'chatbot_channel_id' => $channelModel->id,
                'payload' => [
                    'channel' => $channel->value,
                    'normalized' => $normalized,
                ],
                'created_at' => now(),
                'team_id' => $tenant['team_id'],
                'company_id' => $tenant['company_id'],
            ]);

            return [
                'status' => 'accepted',
                'channel' => $channel->value,
                'chatbot_id' => $chatbot->id,
                'conversation_id' => $conversation->id,
                'history_id' => $history?->id,
            ];
        });
    }
}
