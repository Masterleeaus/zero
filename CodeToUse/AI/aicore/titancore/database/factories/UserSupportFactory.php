<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSupport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UserSupport>
 */
class UserSupportFactory extends Factory
{
    protected $model = UserSupport::class;

    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'user_id'    => $user->id,
            'company_id' => $user->company_id,
            'ticket_id'  => Str::upper(Str::random(10)),
            'priority'   => $this->faker->randomElement(['low', 'medium', 'high']),
            'category'   => $this->faker->randomElement(['general', 'billing', 'technical']),
            'subject'    => $this->faker->sentence(),
            'status'     => 'open',
        ];
    }
}
