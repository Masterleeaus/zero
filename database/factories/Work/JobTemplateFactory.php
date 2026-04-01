<?php

declare(strict_types=1);

namespace Database\Factories\Work;

use App\Models\Work\JobTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobTemplateFactory extends Factory
{
    protected $model = JobTemplate::class;

    public function definition(): array
    {
        return [
            'company_id'   => 1,
            'job_type_id'  => null,
            'team_id'      => null,
            'name'         => $this->faker->words(3, true),
            'instructions' => $this->faker->sentence(),
            'duration'     => $this->faker->randomFloat(2, 0.5, 8),
        ];
    }
}
