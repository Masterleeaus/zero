<?php

namespace Modules\Timesheet\Services\Integrations;

use Modules\Timesheet\Contracts\TaskProvider;

class TasklyTaskProvider implements TaskProvider
{
    public function tasksForProject(int $projectId): array
    {
        if (!function_exists('module_is_active') || !module_is_active('Taskly')) {
            return [];
        }

        $taskClass = '\Modules\Taskly\Entities\Task';
        if (!class_exists($taskClass)) {
            return [];
        }

        return $taskClass::where('project_id', $projectId)
            ->orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }
}
