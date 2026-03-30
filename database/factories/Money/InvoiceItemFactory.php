<?php

declare(strict_types=1);

namespace Database\Factories\Money;

use App\Models\Money\Invoice;
use App\Models\Money\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 5);
        $unitPrice = $this->faker->randomFloat(2, 10, 200);
        $taxRate = $this->faker->randomFloat(2, 0, 20);

        return [
            'company_id' => 1,
            'invoice_id' => Invoice::factory(['company_id' => 1]),
            'description'=> $this->faker->sentence(3),
            'quantity'   => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate'   => $taxRate,
            'line_total' => $quantity * $unitPrice,
            'sort_order' => 0,
        ];
    }
}
