<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a repair suggests the customer could benefit from a service agreement.
 *
 * Corresponds to: crm_agreement_candidate signal.
 */
class CrmAgreementCandidate
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly RepairOrder $repair) {}
}
