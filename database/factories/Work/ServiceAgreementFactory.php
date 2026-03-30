<?php

namespace Database\Factories\Work;

use App\Models\Crm\Customer;
use App\Models\Money\Quote;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceAgreement>
 */
class ServiceAgreementFactory extends Factory
{
    protected $model = ServiceAgreement::class;

    public function definition(): array
    {
        return [
            'company_id'  => 1,
            'customer_id' => Customer::factory(['company_id' => 1]),
            'site_id'     => Site::factory(['company_id' => 1]),
            'quote_id'    => Quote::factory(['company_id' => 1]),
            'title'       => $this->faker->sentence(3),
            'frequency'   => 'monthly',
            'next_run_at' => now()->addMonth(),
            'status'      => 'active',
        ];
    }
}
