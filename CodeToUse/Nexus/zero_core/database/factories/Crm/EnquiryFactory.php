<?php

declare(strict_types=1);

namespace Database\Factories\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnquiryFactory extends Factory
{
    protected $model = Enquiry::class;

    public function definition(): array
    {
        return [
            'company_id'  => 1,
            'customer_id' => Customer::factory(),
            'name'        => $this->faker->sentence(3),
            'email'       => $this->faker->safeEmail,
            'phone'       => $this->faker->phoneNumber,
            'source'      => 'web',
            'status'      => 'open',
        ];
    }
}
