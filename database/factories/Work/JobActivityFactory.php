<?php

declare(strict_types=1);

namespace Database\Factories\Work;

use App\Models\Work\JobActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobActivityFactory extends Factory
{
    protected $model = JobActivity::class;

    public function definition(): array
    {
        return [
            'company_id'     => 1,
            'service_job_id' => null,
            'template_id'    => null,
            'name'           => $this->faker->words(3, true),
            'ref'            => null,
            'sequence'       => $this->faker->numberBetween(0, 100),
            'required'       => false,
            'completed'      => false,
            'state'          => 'todo',
            'completed_by'   => null,
            'completed_on'   => null,
            'assigned_to'    => null,
            'team_id'        => null,
            'follow_up_at'   => null,
        ];
    }

    public function required(): static
    {
        return $this->state(['required' => true]);
    }

    public function done(): static
    {
        return $this->state([
            'state'     => 'done',
            'completed' => true,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(['state' => 'cancel']);
    }
}
