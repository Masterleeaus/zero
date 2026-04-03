<?php

declare(strict_types=1);

namespace App\Events\Sync;

use App\Models\Sync\EdgeSyncConflict;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a sync conflict has been resolved (by user, system, or AI).
 */
class EdgeConflictResolved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly EdgeSyncConflict $conflict,
        public readonly string           $strategy,
    ) {}
}
