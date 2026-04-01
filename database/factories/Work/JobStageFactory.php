<?php

declare(strict_types=1);

namespace Database\Factories\Work;

use App\Models\Work\JobStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobStageFactory extends Factory
{
    protected $model = JobStage::class;

    public function definition(): array
    {
        return [
            'company_id'        => 1,
            'name'              => $this->faker->words(2, true),
            'sequence'          => $this->faker->numberBetween(1, 100),
            'stage_type'        => 'order',
            'is_closed'         => false,
            'is_default'        => false,
            'is_invoiceable'    => false,
            'fold'              => false,
            'require_signature' => false,
            'color'             => '#FFFFFF',
            'description'       => null,
        ];
    }
}
