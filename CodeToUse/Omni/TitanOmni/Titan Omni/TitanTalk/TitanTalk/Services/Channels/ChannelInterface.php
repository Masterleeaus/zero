<?php
namespace Modules\TitanTalk\Services\Channels;

use Illuminate\Http\Request;

interface ChannelInterface {
    /** Handle an inbound webhook, return normalized {text, external_ref, meta[]} */
    public function inbound(Request $request): array;

    /** Send a message back to the external channel */
    public function send(string $externalRef, string $text, array $meta = []): bool;

    /** Channel name (e.g. web, whatsapp, telegram) */
    public static function name(): string;
}
