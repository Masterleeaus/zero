<?php

declare(strict_types=1);

namespace Database\Factories\Money;

use App\Models\Money\Invoice;
use App\Models\Money\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'invoice_id' => Invoice::factory(['company_id' => 1]),
            'amount'     => $this->faker->randomFloat(2, 10, 200),
            'method'     => $this->faker->randomElement(['card', 'cash', 'bank']),
            'reference'  => $this->faker->uuid(),
            'paid_at'    => now(),
        ];
    }
}
