<?php

declare(strict_types=1);

namespace App\Events\Equipment;

use App\Models\Equipment\InstalledEquipment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a piece of installed equipment is replaced by new equipment.
 *
 * Carries both the old (removed) installation and the new installation record.
 *
 * Stage K (fieldservice_equipment_stock) — equipment lifecycle signal.
 */
class EquipmentReplaced
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly InstalledEquipment $oldInstallation,
        public readonly InstalledEquipment $newInstallation,
    ) {}
}
