<?php

namespace Database\Factories\Money;

use App\Models\Money\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'company_id'  => $user->company_id,
            'name'        => $this->faker->word . ' expenses',
            'description' => $this->faker->sentence,
        ];
    }
}
