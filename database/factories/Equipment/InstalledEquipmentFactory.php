<?php

declare(strict_types=1);

namespace Database\Factories\Equipment;

use App\Models\Equipment\Equipment;
use App\Models\Equipment\InstalledEquipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstalledEquipment>
 */
class InstalledEquipmentFactory extends Factory
{
    protected $model = InstalledEquipment::class;

    public function definition(): array
    {
        return [
            'company_id'   => 1,
            'equipment_id' => EquipmentFactory::new(['company_id' => 1]),
            'status'       => 'active',
            'installed_at' => now()->subMonth()->toDateString(),
        ];
    }
}
