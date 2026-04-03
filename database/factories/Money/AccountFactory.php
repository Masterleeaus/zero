<?php

declare(strict_types=1);

namespace Database\Factories\Money;

use App\Models\Money\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'company_id'  => 1,
            'code'        => $this->faker->unique()->numerify('####'),
            'name'        => $this->faker->words(3, true),
            'type'        => $this->faker->randomElement(Account::TYPES),
            'description' => $this->faker->optional()->sentence(),
            'is_active'   => true,
        ];
    }

    public function ofType(string $type): static
    {
        return $this->state(['type' => $type]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
