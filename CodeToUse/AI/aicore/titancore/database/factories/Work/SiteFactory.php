<?php

declare(strict_types=1);

namespace Database\Factories\Work;

use App\Models\Work\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'name'       => $this->faker->company . ' Site',
            'reference'  => $this->faker->unique()->bothify('REF-###'),
            'address'    => $this->faker->address,
            'status'     => 'active',
            'start_date' => $this->faker->date(),
        ];
    }
}
