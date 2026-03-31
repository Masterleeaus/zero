<?php

namespace Database\Factories\Support;

use App\Models\Support\ServiceIssue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceIssue>
 */
class ServiceIssueFactory extends Factory
{
    protected $model = ServiceIssue::class;

    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'company_id'  => $user->company_id,
            'user_id'     => $user->id,
            'subject'     => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status'      => 'open',
            'priority'    => 'medium',
        ];
    }
}
