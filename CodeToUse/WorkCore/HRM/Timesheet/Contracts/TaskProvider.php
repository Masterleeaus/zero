<?php

namespace Modules\Timesheet\Contracts;

interface TaskProvider
{
    public function tasksForProject(int $projectId): array;
}
