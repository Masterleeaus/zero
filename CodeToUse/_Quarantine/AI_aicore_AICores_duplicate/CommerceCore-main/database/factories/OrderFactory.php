<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'customer_name' => $this->faker->name,
            'customer_email' => $this->faker->safeEmail,
            'subtotal' => $total = $this->faker->randomFloat(2, 10, 500),
            'total_price' => $total,
            'status' => 'pending',
            'order_number' => 'ORD-' . strtoupper($this->faker->unique()->lexify('?????')),
        ];
    }
}
