<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairOrder;
use App\Models\Repair\RepairTemplate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a repair template is applied to a repair order.
 *
 * Corresponds to: repair_template_applied signal.
 */
class RepairTemplateApplied
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly RepairTemplate $template,
        public readonly RepairOrder $repair,
    ) {}
}
