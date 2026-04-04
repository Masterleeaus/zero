<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

class CallTransferService
{
    public function targetNumber(): ?string
    {
        return config('unified-communication.transfer.default_number');
    }

    public function canTransfer(): bool
    {
        return filled($this->targetNumber()) && (bool) config('unified-communication.routing.agent_transfer', true);
    }
}
