<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Enums;

enum ChannelTypeEnum: string
{
    case Generic = 'generic';
    case Telegram = 'telegram';
    case WhatsApp = 'whatsapp';
    case Messenger = 'messenger';
    case Voice = 'voice';

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
