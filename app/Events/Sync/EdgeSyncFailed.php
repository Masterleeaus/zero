<?php

declare(strict_types=1);

namespace App\Events\Sync;

use App\Models\Sync\EdgeSyncQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a sync operation fails after exhausting retry attempts.
 */
class EdgeSyncFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly EdgeSyncQueue $queueItem,
        public readonly string        $reason,
    ) {}
}
