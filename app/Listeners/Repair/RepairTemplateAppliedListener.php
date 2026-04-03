<?php

declare(strict_types=1);

namespace App\Listeners\Repair;

use App\Events\Repair\RepairTemplateApplied;
use App\Events\Repair\RepairTemplateGeneratedChecklist;
use App\Events\Repair\RepairTemplateGeneratedParts;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * React to a repair template being applied to a repair order.
 *
 * Emits secondary signals when the template generates checklists and parts.
 */
class RepairTemplateAppliedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(RepairTemplateApplied $event): void
    {
        $template = $event->template;
        $repair   = $event->repair;

        try {
            if ($template->checklists->isNotEmpty()) {
                RepairTemplateGeneratedChecklist::dispatch($template, $repair);
            }

            if ($template->parts->isNotEmpty()) {
                RepairTemplateGeneratedParts::dispatch($template, $repair);
            }
        } catch (\Throwable $th) {
            Log::error('RepairTemplateAppliedListener: ' . $th->getMessage(), [
                'template_id' => $template->id,
                'repair_id'   => $repair->id,
            ]);
        }
    }
}
