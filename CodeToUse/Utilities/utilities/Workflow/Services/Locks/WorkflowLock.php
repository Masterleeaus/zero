<?php

namespace Modules\Workflow\Services\Locks;

use Illuminate\Support\Facades\Cache;

class WorkflowLock
{
    public function acquire(string $key, int $ttlSeconds = 30): bool
    {
        return Cache::add('workflow:lock:' . $key, 1, $ttlSeconds);
    }

    public function release(string $key): void
    {
        Cache::forget('workflow:lock:' . $key);
    }
}
