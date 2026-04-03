<?php

namespace Modules\Timesheet\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Timesheet\Entities\Timesheet;

class TimesheetFactory extends Factory
{
    protected $model = Timesheet::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'project_id' => null,
            'task_id' => null,
            'date' => now()->toDateString(),
            'hours' => 1,
            'minutes' => 0,
            'type' => 'work',
            'notes' => $this->faker->sentence,
            'workspace_id' => 1,
            'created_by' => 1,
        ];
    }
}
