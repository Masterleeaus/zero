<?php

namespace Modules\Timesheet\Services\Integrations;

use Illuminate\Support\Facades\DB;
use Modules\Timesheet\Contracts\WorkOrderProvider;

class CoreWorkOrderProvider implements WorkOrderProvider
{
    public function listForSelect(?int $companyId, ?int $projectId = null, ?string $search = null, int $limit = 50): array
    {
        $table = config('timesheet.integrations.core_work_orders_table', 'work_orders');
        $idCol = config('timesheet.integrations.core_work_orders_id_column', 'id');
        $titleCol = config('timesheet.integrations.core_work_orders_title_column', 'title');
        $companyCol = config('timesheet.integrations.core_work_orders_company_column', 'company_id');
        $projectCol = config('timesheet.integrations.core_work_orders_project_column', 'project_id');

        $q = DB::table($table)->select([$idCol.' as id', $titleCol.' as label']);

        if ($companyId && $companyCol) {
            $q->where($companyCol, $companyId);
        }
        if ($projectId && $projectCol) {
            $q->where($projectCol, $projectId);
        }
        if ($search) {
            $q->where($titleCol, 'like', '%'.$search.'%');
        }

        $rows = $q->orderBy($titleCol)->limit($limit)->get();

        return $rows->map(fn($r) => ['id' => $r->id, 'label' => (string) $r->label])->all();
    }

    public function label(?int $companyId, $workOrderId): ?string
    {
        if (!$workOrderId) {
            return null;
        }

        $table = config('timesheet.integrations.core_work_orders_table', 'work_orders');
        $idCol = config('timesheet.integrations.core_work_orders_id_column', 'id');
        $titleCol = config('timesheet.integrations.core_work_orders_title_column', 'title');
        $companyCol = config('timesheet.integrations.core_work_orders_company_column', 'company_id');

        $q = DB::table($table)->where($idCol, $workOrderId);

        if ($companyId && $companyCol) {
            $q->where($companyCol, $companyId);
        }

        $row = $q->first([$titleCol.' as label']);

        return $row ? (string) $row->label : null;
    }
}
