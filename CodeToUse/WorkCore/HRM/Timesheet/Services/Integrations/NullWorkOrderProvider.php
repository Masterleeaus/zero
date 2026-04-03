<?php

namespace Modules\Timesheet\Services\Integrations;

use Modules\Timesheet\Contracts\WorkOrderProvider;

class NullWorkOrderProvider implements WorkOrderProvider
{
    public function listForSelect(?int $companyId, ?int $projectId = null, ?string $search = null, int $limit = 50): array
    {
        return [];
    }

    public function label(?int $companyId, $workOrderId): ?string
    {
        return null;
    }
}
