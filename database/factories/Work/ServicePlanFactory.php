<?php

declare(strict_types=1);

namespace Database\Factories\Work;

use App\Models\Work\ServicePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicePlan>
 */
class ServicePlanFactory extends Factory
{
    protected $model = ServicePlan::class;

    public function definition(): array
    {
        return [
            'company_id'           => 1,
            'name'                 => $this->faker->sentence(3),
            'frequency'            => 'monthly',
            'status'               => 'active',
            'is_active'            => true,
            'auto_generate_visits' => true,
            'recurrence_type'      => 'maintenance',
            'starts_on'            => now()->toDateString(),
            'next_visit_due'       => now()->addMonth()->toDateString(),
        ];
    }
}
