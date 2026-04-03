<?php

namespace Modules\Timesheet\Contracts;

interface WorkOrderProvider
{
    /**
     * Return an array of work orders for select dropdowns.
     * Each item: ['id' => int|string, 'label' => string]
     */
    public function listForSelect(?int $companyId, ?int $projectId = null, ?string $search = null, int $limit = 50): array;

    /**
     * Resolve a work order label for display.
     */
    public function label(?int $companyId, $workOrderId): ?string;
}
