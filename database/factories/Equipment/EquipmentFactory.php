<?php

declare(strict_types=1);

namespace Database\Factories\Equipment;

use App\Models\Equipment\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'company_id'    => 1,
            'name'          => $this->faker->words(3, true),
            'model'         => $this->faker->bothify('Model-##??'),
            'manufacturer'  => $this->faker->company(),
            'serial_number' => $this->faker->unique()->bothify('SN-#####'),
            'category'      => $this->faker->randomElement(['hvac', 'pump', 'sensor', 'electrical']),
            'status'        => 'in_stock',
        ];
    }
}
