<?php

declare(strict_types=1);

namespace App\Events\Premises;

use App\Models\Premises\Hazard;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a hazard is resolved (status changed to resolved).
 *
 * Stage C — Hazard lifecycle signal.
 */
class HazardResolved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Hazard $hazard) {}
}
