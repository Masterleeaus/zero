<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

class LifecycleStageResolver
{
    public function resolve(?string $intent): string
    {
        $map = (array) config('titan-personas.lifecycle_map', []);

        return (string) ($map[$intent ?? ''] ?? 'support');
    }
}
