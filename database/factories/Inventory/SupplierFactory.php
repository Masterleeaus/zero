<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'company_id'    => 1,
            'name'          => $this->faker->company(),
            'email'         => $this->faker->safeEmail(),
            'phone'         => $this->faker->phoneNumber(),
            'address'       => $this->faker->streetAddress(),
            'city'          => $this->faker->city(),
            'country'       => 'AU',
            'currency_code' => 'AUD',
            'status'        => 'active',
        ];
    }
}
