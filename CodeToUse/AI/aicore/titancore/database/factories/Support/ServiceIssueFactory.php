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
        return [
            'user_id'     => static fn () => User::factory()->create()->id,
            'company_id'  => static function (array $attributes) {
                if (isset($attributes['company_id'])) {
                    return $attributes['company_id'];
                }

                if (isset($attributes['user_id'])) {
                    return User::find($attributes['user_id'])?->company_id;
                }

                return User::factory()->create()->company_id;
            },
            'subject'     => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status'      => 'open',
            'priority'    => 'medium',
        ];
    }
}
