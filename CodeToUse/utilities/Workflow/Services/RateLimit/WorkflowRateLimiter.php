<?php

namespace Modules\Workflow\Services\RateLimit;

use Illuminate\Support\Facades\Cache;

class WorkflowRateLimiter
{
    public function allow(string $bucketKey, int $max = 25, int $perSeconds = 60): bool
    {
        $key = 'workflow:rl:' . $bucketKey;
        $count = (int) Cache::get($key, 0);

        if ($count >= $max) {
            return false;
        }

        if ($count === 0) {
            Cache::put($key, 1, $perSeconds);
        } else {
            Cache::increment($key);
        }

        return true;
    }
}
