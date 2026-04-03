<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word;
        return [
            'name' => ucfirst($name),
            'slug' => $name,
            'price' => 29.00,
            'max_products' => 500,
            'max_pages' => 10,
            'features' => ['all'],
            'is_active' => true,
        ];
    }
}
