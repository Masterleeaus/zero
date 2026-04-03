<?php

namespace Modules\Timesheet\Services;

use Illuminate\Support\Facades\Schema;
use Modules\Timesheet\Contracts\HrmProvider;
use Modules\Timesheet\Contracts\TaskProvider;
use Modules\Timesheet\Contracts\WorkOrderProvider;
use Modules\Timesheet\Services\Integrations\CoreHrmProvider;
use Modules\Timesheet\Services\Integrations\CoreTaskProvider;
use Modules\Timesheet\Services\Integrations\CoreWorkOrderProvider;
use Modules\Timesheet\Services\Integrations\NullHrmProvider;
use Modules\Timesheet\Services\Integrations\NullWorkOrderProvider;
use Modules\Timesheet\Services\Integrations\TasklyTaskProvider;

class TimesheetIntegrationResolver
{
    public function tasks(): TaskProvider
    {
        return $this->taskProvider();
    }

    public function hrm(): HrmProvider
    {
        return $this->hrmProvider();
    }

    public function workOrders(): WorkOrderProvider
    {
        return $this->workOrderProvider();
    }

    public function taskProvider(): TaskProvider
    {
        // Prefer core Tasks table (default 'tasks') if it exists.
        $table = config('timesheet.integrations.core_tasks_table', 'tasks');
        if ($table && Schema::hasTable($table)) {
            return new CoreTaskProvider();
        }

        // Optional Taskly integration if present.
        if (function_exists('module_is_active') && module_is_active('Taskly') && class_exists('Modules\Taskly\Entities\Task')) {
            return new TasklyTaskProvider();
        }

        return new CoreTaskProvider(); // will safely return empty if table missing
    }

    public function hrmProvider(): HrmProvider
    {
        $table = config('timesheet.integrations.core_hrm_table', 'employees');
        if ($table && Schema::hasTable($table)) {
            return new CoreHrmProvider();
        }

        return new NullHrmProvider();
    }

    public function workOrderProvider(): WorkOrderProvider
    {
        $table = config('timesheet.integrations.core_work_orders_table', 'work_orders');
        if ($table && Schema::hasTable($table)) {
            return new CoreWorkOrderProvider();
        }

        return new NullWorkOrderProvider();
    }
}
