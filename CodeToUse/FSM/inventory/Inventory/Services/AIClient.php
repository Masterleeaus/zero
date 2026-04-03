<?php

namespace Modules\Inventory\Services;

use GuzzleHttp\Client;

class AIClient
{
    public function chat(array $messages): string
    {
        $key = config('inventory.ai.openai.api_key');
        if (!$key) {
            return 'AI not configured';
        }
        // Intentionally simple: integration details belong in host app/service
        return 'AI call stub; wire to openai-php/client in host app';
    }
}
