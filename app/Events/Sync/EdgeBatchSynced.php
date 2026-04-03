<?php

declare(strict_types=1);

namespace App\Events\Sync;

use App\Models\Sync\EdgeSyncLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a complete sync batch has been successfully processed.
 */
class EdgeBatchSynced
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly EdgeSyncLog $syncLog,
    ) {}
}
