<?php

declare(strict_types=1);

namespace App\Events\Repair;

use App\Models\Repair\RepairOrder;
use App\Models\Repair\RepairTemplate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a template generates checklists for a repair order.
 *
 * Corresponds to: repair_template_generated_checklist signal.
 */
class RepairTemplateGeneratedChecklist
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly RepairTemplate $template,
        public readonly RepairOrder $repair,
    ) {}
}
