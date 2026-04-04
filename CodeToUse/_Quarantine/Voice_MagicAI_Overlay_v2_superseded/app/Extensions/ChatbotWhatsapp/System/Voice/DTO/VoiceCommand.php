<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\DTO;

final class VoiceCommand
{
    public function __construct(
        public readonly string $intent,
        public readonly array $entities,
        public readonly float $confidence,
        public readonly array $missing,
        public readonly string $originalTranscript,
    ) {}

    public function shouldExecuteImmediately(float $threshold = 0.90): bool
    {
        return $this->confidence >= $threshold && $this->missing === [];
    }

    public function requiresConfirmation(float $threshold = 0.60): bool
    {
        return $this->confidence >= $threshold && $this->missing === [];
    }

    public function isIncomplete(): bool
    {
        return $this->missing !== [];
    }
}
