<?php

declare(strict_types=1);

namespace Database\Factories\Work;

use App\Models\Work\Checklist;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChecklistFactory extends Factory
{
    protected $model = Checklist::class;

    public function definition(): array
    {
        return [
            'company_id'     => 1,
            'service_job_id' => ServiceJob::factory(['company_id' => 1]),
            'title'          => $this->faker->sentence(4),
            'is_completed'   => false,
        ];
    }
}
