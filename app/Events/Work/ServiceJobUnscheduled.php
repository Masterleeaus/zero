<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceJob's scheduled_date_start is cleared (set to null).
 *
 * Calendar adapters should remove the event from their surfaces on receipt.
 *
 * Module 9 (fieldservice_calendar) — calendar lifecycle signal.
 */
class ServiceJobUnscheduled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly ?string $previousStart = null,
    ) {}
}
