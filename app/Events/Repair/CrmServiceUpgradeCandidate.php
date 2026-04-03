<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a repair reveals an opportunity to sell a service upgrade.
 *
 * Corresponds to: crm_service_upgrade_candidate signal.
 */
class CrmServiceUpgradeCandidate
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly RepairOrder $repair) {}
}
