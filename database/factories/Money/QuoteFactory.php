<?php

declare(strict_types=1);

namespace Database\Factories\Money;

use App\Models\Money\Quote;
use App\Models\Crm\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'customer_id' => Customer::factory(['company_id' => 1]),
            'number'     => $this->faker->unique()->bothify('Q-####'),
            'title'      => $this->faker->sentence(3),
            'status'     => 'draft',
            'issue_date' => $this->faker->date(),
            'due_date'   => $this->faker->date(),
            'currency'   => 'USD',
            'total'      => $this->faker->randomFloat(2, 100, 1000),
        ];
    }
}
