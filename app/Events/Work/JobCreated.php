<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceJob is first created.
 *
 * MODULE 08 — DocsExecutionBridge listens to this event to inject
 * relevant procedure documents at job creation time.
 */
class JobCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ServiceJob $job) {}
}
