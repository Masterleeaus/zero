<?php

namespace Modules\Workflow\Services\Idempotency;

class IdempotencyKey
{
    public static function make(string $triggerKey, array $payload, array $meta = []): string
    {
        $companyId = $meta['company_id'] ?? ($payload['company_id'] ?? null);
        $modelClass = $payload['model_class'] ?? '';
        $modelId = $payload['model_id'] ?? '';
        return sha1(implode('|', [
            (string)$companyId,
            $triggerKey,
            (string)$modelClass,
            (string)$modelId,
            json_encode($payload),
        ]));
    }
}
