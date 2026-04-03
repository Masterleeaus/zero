<?php

declare(strict_types=1);

namespace Database\Factories\Crm;

use App\Models\Crm\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'name'       => $this->faker->company,
            'email'      => $this->faker->unique()->safeEmail,
            'phone'      => $this->faker->phoneNumber,
            'status'     => 'active',
        ];
    }
}
