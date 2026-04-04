<?php

namespace Database\Factories\Work;

use App\Models\Crm\Customer;
use App\Models\Work\FieldServiceAgreement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FieldServiceAgreement>
 */
class FieldServiceAgreementFactory extends Factory
{
    protected $model = FieldServiceAgreement::class;

    public function definition(): array
    {
        return [
            'company_id'           => 1,
            'customer_id'          => Customer::factory(['company_id' => 1]),
            'title'                => $this->faker->sentence(3),
            'reference'            => 'FSA-' . $this->faker->unique()->numberBetween(1000, 9999),
            'start_date'           => now()->toDateString(),
            'end_date'             => now()->addYear()->toDateString(),
            'billing_cycle'        => 'monthly',
            'service_frequency'    => 'monthly',
            'status'               => 'active',
            'auto_generate_jobs'   => false,
            'auto_generate_visits' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function expired(): static
    {
        return $this->state([
            'status'   => 'expired',
            'end_date' => now()->subDay()->toDateString(),
        ]);
    }
}
