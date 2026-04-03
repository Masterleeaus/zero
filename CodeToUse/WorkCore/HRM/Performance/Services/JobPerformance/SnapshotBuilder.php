<?php

namespace Modules\Performance\Services\JobPerformance;

use Modules\Performance\Entities\JobPerformanceSnapshot;
use Modules\Performance\Entities\Objective;

class SnapshotBuilder
{
    public function fromObjective(Objective $objective): JobPerformanceSnapshot
    {
        return JobPerformanceSnapshot::updateOrCreate(
            ['objective_id' => $objective->id],
            [
                'user_id' => $objective->added_by ?? null,
                'project_id' => $objective->project_id ?? null,
                'job_id' => $objective->job_id ?? null,
                'jobsite_id' => $objective->jobsite_id ?? null,
                'work_order_id' => $objective->work_order_id ?? null,
                'status' => 'draft',
            ]
        );
    }
}
