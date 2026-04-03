<?php

namespace Modules\Timesheet\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimesheetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'task_id' => $this->task_id,
            'work_order_id' => $this->work_order_id,
            'date' => optional($this->date)->toDateString(),
            'hours' => $this->hours,
            'minutes' => $this->minutes,
            'type' => $this->type,
            'notes' => $this->notes,
            'fsm_rate_per_hour' => $this->fsm_rate_per_hour,
            'fsm_overtime_multiplier' => $this->fsm_overtime_multiplier,
            'fsm_cost_total' => $this->fsm_cost_total,
        ];
    }
}
