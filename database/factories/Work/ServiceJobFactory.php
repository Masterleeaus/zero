<?php

declare(strict_types=1);

namespace Database\Factories\Work;

use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceJobFactory extends Factory
{
    protected $model = ServiceJob::class;

    public function definition(): array
    {
        return [
            'company_id'   => 1,
            'site_id'      => Site::factory(['company_id' => 1]),
            'title'        => $this->faker->sentence(3),
            'status'       => 'scheduled',
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+3 days'),
        ];
    }
}
