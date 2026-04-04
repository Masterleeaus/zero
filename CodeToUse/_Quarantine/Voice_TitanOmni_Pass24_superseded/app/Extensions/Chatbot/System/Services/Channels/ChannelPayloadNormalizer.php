<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Services\Channels;

use App\Extensions\Chatbot\System\Enums\ChannelTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ChannelPayloadNormalizer
{
    public function normalize(ChannelTypeEnum|string $channel, Request $request): array
    {
        $channel = $channel instanceof ChannelTypeEnum ? $channel : ChannelTypeEnum::from($channel);
        $payload = $request->all();

        return match ($channel) {
            ChannelTypeEnum::Telegram => [
                'channel' => $channel->value,
                'external_message_id' => (string) (Arr::get($payload, 'message.message_id') ?? Arr::get($payload, 'edited_message.message_id') ?? ''),
                'external_customer_id' => (string) (Arr::get($payload, 'message.from.id') ?? Arr::get($payload, 'edited_message.from.id') ?? Arr::get($payload, 'callback_query.from.id') ?? ''),
                'name' => trim((string) ((Arr::get($payload, 'message.from.first_name', '') . ' ' . Arr::get($payload, 'message.from.last_name', '')) ?: Arr::get($payload, 'callback_query.from.username', 'Telegram User'))),
                'message' => (string) (Arr::get($payload, 'message.text') ?? Arr::get($payload, 'edited_message.text') ?? Arr::get($payload, 'callback_query.data') ?? ''),
                'raw' => $payload,
            ],
            ChannelTypeEnum::WhatsApp => [
                'channel' => $channel->value,
                'external_message_id' => (string) (Arr::get($payload, 'messages.0.id') ?? Arr::get($payload, 'entry.0.changes.0.value.messages.0.id') ?? ''),
                'external_customer_id' => (string) (Arr::get($payload, 'contacts.0.wa_id') ?? Arr::get($payload, 'entry.0.changes.0.value.contacts.0.wa_id') ?? ''),
                'name' => (string) (Arr::get($payload, 'contacts.0.profile.name') ?? Arr::get($payload, 'entry.0.changes.0.value.contacts.0.profile.name') ?? 'WhatsApp User'),
                'message' => (string) (Arr::get($payload, 'messages.0.text.body') ?? Arr::get($payload, 'entry.0.changes.0.value.messages.0.text.body') ?? ''),
                'raw' => $payload,
            ],
            ChannelTypeEnum::Messenger => [
                'channel' => $channel->value,
                'external_message_id' => (string) (Arr::get($payload, 'entry.0.messaging.0.message.mid') ?? ''),
                'external_customer_id' => (string) (Arr::get($payload, 'entry.0.messaging.0.sender.id') ?? ''),
                'name' => (string) (Arr::get($payload, 'entry.0.messaging.0.sender.id') ?? 'Messenger User'),
                'message' => (string) (Arr::get($payload, 'entry.0.messaging.0.message.text') ?? ''),
                'raw' => $payload,
            ],
            ChannelTypeEnum::Voice => [
                'channel' => $channel->value,
                'external_message_id' => (string) (Arr::get($payload, 'call_id') ?? Arr::get($payload, 'conversation_id') ?? ''),
                'external_customer_id' => (string) (Arr::get($payload, 'from') ?? Arr::get($payload, 'caller') ?? ''),
                'name' => (string) (Arr::get($payload, 'caller_name') ?? Arr::get($payload, 'from') ?? 'Voice Caller'),
                'message' => (string) (Arr::get($payload, 'transcript') ?? Arr::get($payload, 'speech_to_text') ?? Arr::get($payload, 'utterance') ?? ''),
                'raw' => $payload,
            ],
            default => [
                'channel' => ChannelTypeEnum::Generic->value,
                'external_message_id' => (string) (Arr::get($payload, 'message_id') ?? Arr::get($payload, 'id') ?? ''),
                'external_customer_id' => (string) (Arr::get($payload, 'customer_id') ?? Arr::get($payload, 'sender.id') ?? Arr::get($payload, 'from') ?? ''),
                'name' => (string) (Arr::get($payload, 'name') ?? Arr::get($payload, 'sender.name') ?? 'External User'),
                'message' => (string) (Arr::get($payload, 'message') ?? Arr::get($payload, 'text') ?? ''),
                'raw' => $payload,
            ],
        };
    }
}
