<?php

namespace App\Services\Omni;

use App\Services\Omni\ChannelAdapters\ChatbotAdapter;
use App\Services\Omni\ChannelAdapters\MessengerAdapter;
use App\Services\Omni\ChannelAdapters\TelegramAdapter;
use App\Services\Omni\ChannelAdapters\VoiceAdapter;
use App\Services\Omni\ChannelAdapters\WhatsappAdapter;
use InvalidArgumentException;

class OmniAdapterRegistry
{
    public function __construct(
        protected ChatbotAdapter $chatbot,
        protected WhatsappAdapter $whatsapp,
        protected TelegramAdapter $telegram,
        protected MessengerAdapter $messenger,
        protected VoiceAdapter $voice
    ) {
    }

    public function for(string $driver): object
    {
        return match ($driver) {
            'chatbot' => $this->chatbot,
            'whatsapp' => $this->whatsapp,
            'telegram' => $this->telegram,
            'messenger' => $this->messenger,
            'voice' => $this->voice,
            default => throw new InvalidArgumentException("Unsupported Omni driver [{$driver}]"),
        };
    }
}
