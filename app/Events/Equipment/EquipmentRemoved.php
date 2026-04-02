<?php

declare(strict_types=1);

namespace App\Events\Equipment;

use App\Models\Equipment\InstalledEquipment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a piece of installed equipment is removed from a site/premises.
 *
 * Stage K (fieldservice_equipment_stock) — equipment lifecycle signal.
 */
class EquipmentRemoved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly InstalledEquipment $installation) {}
}
