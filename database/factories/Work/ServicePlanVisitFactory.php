<?php

declare(strict_types=1);

namespace Database\Factories\Work;

use App\Models\Work\ServicePlanVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicePlanVisit>
 */
class ServicePlanVisitFactory extends Factory
{
    protected $model = ServicePlanVisit::class;

    public function definition(): array
    {
        return [
            'company_id'     => 1,
            'status'         => 'pending',
            'visit_type'     => 'maintenance',
            'scheduled_date' => now()->addDays(rand(1, 30))->toDateString(),
            'coverage_source' => 'agreement',
        ];
    }
}
