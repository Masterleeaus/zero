<?php

namespace Modules\Timesheet\Services\Integrations;

use Illuminate\Support\Facades\DB;
use Modules\Timesheet\Contracts\TaskProvider;

class CoreTaskProvider implements TaskProvider
{
    public function tasksForProject(int $projectId): array
    {
        $table = config('timesheet.integrations.core_tasks_table', 'tasks');
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return [];
        }

        $titleCol = config('timesheet.integrations.core_tasks_title_column', 'title');
        $projectCol = config('timesheet.integrations.core_tasks_project_column', 'project_id');

        return DB::table($table)
            ->where($projectCol, $projectId)
            ->orderBy($titleCol)
            ->pluck($titleCol, 'id')
            ->toArray();
    }
}
