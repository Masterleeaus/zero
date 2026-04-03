<?php

declare(strict_types=1);

namespace App\Events\Premises;

use App\Models\Premises\Hazard;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a new hazard is detected and recorded against a premises.
 *
 * Stage C — Hazard lifecycle signal.
 */
class HazardDetected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Hazard $hazard) {}
}
