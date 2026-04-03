<?php

namespace Modules\Timesheet\Traits;

trait ComputesCosting
{
    public function computeCosting(): void
    {
        $hours = method_exists($this, 'computedHours') ? $this->computedHours() : ($this->hours ?? 0);
        $rate  = $this->fsm_rate_per_hour ?? optional($this->user)->hourly_rate ?? 0;
        $mult  = $this->fsm_overtime_multiplier ?: 1.0;
        $this->fsm_cost_total = round(($hours ?: 0) * ($rate ?: 0) * ($mult ?: 1.0), 2);
    }

    protected static function bootComputesCosting(): void
    {
        static::saving(function ($model) {
            $model->computeCosting();
        });
    }

    public function approve(): void
    {
        $this->approved_at = now();
        $this->save();
        event(new \Modules\Workflow\Events\TimesheetApproved($this));
    }

    public function submitForApproval(): void
    {
        event(new \Modules\Workflow\Events\TimesheetSubmitted($this));
    }
}
