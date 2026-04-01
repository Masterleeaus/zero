<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        $name = $this->faker->company;
        return [
            'owner_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'domain' => Str::slug($name) . '.test',
            'status' => 'active',
            'plan_id' => Plan::factory(),
        ];
    }
}
