<?php

declare(strict_types=1);

namespace Database\Factories\Vehicle;

use App\Models\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'company_id'        => 1,
            'name'              => $this->faker->word() . ' Van',
            'registration'      => strtoupper($this->faker->bothify('???-###')),
            'vehicle_type'      => $this->faker->randomElement(Vehicle::TYPES),
            'team_id'           => null,
            'assigned_driver_id' => null,
            'make'              => $this->faker->randomElement(['Ford', 'Toyota', 'Mercedes', 'Volkswagen']),
            'model'             => $this->faker->word(),
            'year'              => $this->faker->year(),
            'capacity_kg'       => $this->faker->randomElement([500, 1000, 2000, 3500]),
            'capability_tags'   => [],
            'status'            => Vehicle::STATUS_ACTIVE,
            'notes'             => null,
        ];
    }
}
