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
            'customer_id'  => null,
            'quote_id'     => null,
            'agreement_id' => null,
            'assigned_user_id' => null,
            'title'        => $this->faker->sentence(3),
            'status'       => 'scheduled',
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+3 days'),
        ];
    }
}
