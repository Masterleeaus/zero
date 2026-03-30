<?php

declare(strict_types=1);

namespace Database\Factories\Money;

use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\Crm\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 80, 900);
        $tax = $this->faker->randomFloat(2, 5, 50);
        $total = $subtotal + $tax;

        return [
            'company_id'  => 1,
            'customer_id' => Customer::factory(['company_id' => 1]),
            'quote_id'    => Quote::factory(['company_id' => 1]),
            'invoice_number' => $this->faker->unique()->bothify('INV-####'),
            'title'       => $this->faker->sentence(3),
            'status'      => 'draft',
            'issue_date'  => $this->faker->date(),
            'due_date'    => $this->faker->date(),
            'currency'    => 'USD',
            'subtotal'    => $subtotal,
            'tax'         => $tax,
            'total'       => $total,
            'paid_amount' => 0,
            'balance'     => $total,
        ];
    }
}
