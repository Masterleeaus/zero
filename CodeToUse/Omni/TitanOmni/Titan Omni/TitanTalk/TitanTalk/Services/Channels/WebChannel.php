<?php
namespace Modules\TitanTalk\Services\Channels;

use Illuminate\Http\Request;

class WebChannel implements ChannelInterface {
    public static function name(): string { return 'web'; }

    public function inbound(Request $request): array {
        return [
            'text' => (string)$request->input('message',''),
            'external_ref' => (string)$request->input('session','web:'+session()->getId()),
            'meta' => ['ip'=>$request->ip()],
        ];
    }

    public function send(string $externalRef, string $text, array $meta = []): bool {
        // For web widget we just return via API; real-time push can be added via pusher/socket later
        return true;
    }
}
