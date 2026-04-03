<?php

namespace Modules\Workflow\Services\Idempotency;

use Illuminate\Support\Facades\Cache;

class IdempotencyStore
{
    public function acquire(string $key, int $ttlSeconds = 60): bool
    {
        return Cache::add('workflow:idemp:' . $key, 1, $ttlSeconds);
    }
}
