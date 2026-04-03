<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServicePlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an existing recurring ServicePlan is updated or regenerated.
 *
 * Covers agreement modification triggers that cause recurrence regeneration.
 */
class RecurringPlanUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ServicePlan $plan) {}
}
