<?php

namespace Database\Factories\Money;

use App\Models\Money\Expense;
use App\Models\Money\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $category = ExpenseCategory::factory()->create();

        return [
            'company_id'           => $category->company_id,
            'expense_category_id'  => $category->id,
            'created_by'           => User::factory()->create(['company_id' => $category->company_id])->id,
            'title'                => $this->faker->sentence(3),
            'amount'               => $this->faker->randomFloat(2, 10, 500),
            'expense_date'         => now()->toDateString(),
            'notes'                => $this->faker->sentence,
        ];
    }
}
