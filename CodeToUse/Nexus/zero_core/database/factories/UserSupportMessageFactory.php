<?php

namespace Database\Factories;

use App\Models\UserSupport;
use App\Models\UserSupportMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSupportMessage>
 */
class UserSupportMessageFactory extends Factory
{
    protected $model = UserSupportMessage::class;

    public function definition(): array
    {
        $support = UserSupport::factory()->create();

        return [
            'user_support_id' => $support->id,
            'company_id'      => $support->company_id,
            'message'         => $this->faker->sentence(),
            'sender'          => 'user',
        ];
    }
}
