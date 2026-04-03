<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Equipment\InstalledEquipment;
use App\Models\Work\ServicePlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a recurring service plan is created for a specific installed equipment unit.
 *
 * Mirrors Odoo fieldservice_sale_recurring + fieldservice_sale_agreement_equipment_stock:
 *   equipment sold through agreement → maintenance recurrence created.
 */
class RecurringEquipmentServiceCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServicePlan $plan,
        public readonly InstalledEquipment $installedEquipment,
    ) {}
}
