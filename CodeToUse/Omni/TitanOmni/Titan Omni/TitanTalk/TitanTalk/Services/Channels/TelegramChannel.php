<?php
namespace Modules\TitanTalk\Services\Channels;

use Illuminate\Http\Request;

class TelegramChannel implements ChannelInterface {
    public static function name(): string { return 'telegram'; }

    public function inbound(Request $request): array {
        $payload = $request->json()->all() ?: $request->all();
        $chatId = $payload['message']['chat']['id'] ?? $payload['edited_message']['chat']['id'] ?? null;
        $text = $payload['message']['text'] ?? $payload['edited_message']['text'] ?? '';
        return [
            'text' => (string)$text,
            'external_ref' => (string)$chatId,
            'meta' => ['raw'=>$payload],
        ];
    }

    public function send(string $externalRef, string $text, array $meta = []): bool {
        // Placeholder: call Telegram bot sendMessage API with chat_id=$externalRef, text=$text
        return true;
    }
}
