<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\DTO;

class UnifiedActionResult
{
    public function __construct(
        public bool $handled,
        public string $response,
        public ?string $intent = null,
        public ?string $persona = null,
        public array $payload = [],
        public bool $requiresConfirmation = false,
        public bool $usedFallback = false,
    ) {}
}
