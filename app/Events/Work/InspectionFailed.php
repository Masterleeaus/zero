<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\InspectionInstance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an InspectionInstance is marked as failed.
 *
 * Stage K (fieldservice_inspection) — inspection lifecycle signal.
 */
class InspectionFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly InspectionInstance $inspection) {}
}
