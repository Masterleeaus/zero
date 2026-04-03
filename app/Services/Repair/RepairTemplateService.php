<?php

declare(strict_types=1);

namespace App\Services\Repair;

use App\Events\Repair\RepairTemplateApplied;
use App\Models\Repair\RepairChecklist;
use App\Models\Repair\RepairOrder;
use App\Models\Repair\RepairPartUsage;
use App\Models\Repair\RepairTask;
use App\Models\Repair\RepairTemplate;
use Illuminate\Support\Str;

/**
 * RepairTemplateService
 *
 * FSM Module 10 — Repair Template Engine
 *
 * Handles the instantiation of repair templates into concrete RepairOrders.
 * Generates tasks, checklists, parts lists, and default diagnosis hints
 * from a template definition.
 */
class RepairTemplateService
{
    /**
     * Create a new RepairOrder from a template, merging any provided attributes.
     *
     * Automatically instantiates tasks, parts, and checklists from the template.
     */
    public function createRepairOrder(RepairTemplate $template, array $attributes = []): RepairOrder
    {
        $defaults = [
            'repair_number'     => $this->generateRepairNumber(),
            'repair_template_id' => $template->id,
            'company_id'        => $template->company_id,
            'created_by'        => $attributes['created_by'] ?? $template->created_by,
            'repair_status'     => RepairOrder::STATUS_DRAFT,
            'repair_type'       => RepairOrder::TYPE_CORRECTIVE,
            'priority'          => RepairOrder::PRIORITY_NORMAL,
            'requires_parts'    => $template->parts->isNotEmpty(),
        ];

        /** @var RepairOrder $order */
        $order = RepairOrder::create(array_merge($defaults, $attributes));

        $this->applyToRepairOrder($template, $order);

        return $order;
    }

    /**
     * Apply a template to an existing RepairOrder.
     *
     * Generates tasks from template steps, parts from template parts,
     * and checklists from template checklists.
     */
    public function applyToRepairOrder(RepairTemplate $template, RepairOrder $order): void
    {
        $template->loadMissing(['steps', 'parts', 'checklists']);

        $this->generateTasks($template, $order);
        $this->generatePartUsages($template, $order);
        $this->generateChecklists($template, $order);

        RepairTemplateApplied::dispatch($template, $order);
    }

    /**
     * Generate RepairTask records from template steps.
     */
    private function generateTasks(RepairTemplate $template, RepairOrder $order): void
    {
        foreach ($template->steps as $step) {
            RepairTask::create([
                'company_id'      => $order->company_id,
                'created_by'      => $order->created_by,
                'repair_order_id' => $order->id,
                'title'           => $step->title,
                'description'     => $step->description,
                'status'          => 'pending',
                'sequence'        => $step->sequence,
            ]);
        }
    }

    /**
     * Generate RepairPartUsage records from template parts.
     */
    private function generatePartUsages(RepairTemplate $template, RepairOrder $order): void
    {
        foreach ($template->parts as $part) {
            RepairPartUsage::create([
                'company_id'      => $order->company_id,
                'created_by'      => $order->created_by,
                'repair_order_id' => $order->id,
                'part_name'       => $part->part_name,
                'part_sku'        => $part->part_sku,
                'quantity'        => $part->quantity,
                'unit_cost'       => $part->unit_cost,
                'reserved'        => false,
                'consumed'        => false,
            ]);
        }
    }

    /**
     * Generate RepairChecklist records from template checklists.
     */
    private function generateChecklists(RepairTemplate $template, RepairOrder $order): void
    {
        foreach ($template->checklists as $checklist) {
            $items = $checklist->items ?? [];

            RepairChecklist::create([
                'company_id'      => $order->company_id,
                'created_by'      => $order->created_by,
                'repair_order_id' => $order->id,
                'title'           => $checklist->title,
                'checklist_type'  => $checklist->checklist_type,
                'status'          => 'pending',
                'items_total'     => count($items),
                'items_completed' => 0,
                'items_failed'    => 0,
            ]);
        }
    }

    /**
     * Generate a unique repair number in the format REP-YYYYMMDD-XXXXX.
     */
    public function generateRepairNumber(): string
    {
        $date = now()->format('Ymd');
        $rand = strtoupper(Str::random(5));

        return "REP-{$date}-{$rand}";
    }
}
