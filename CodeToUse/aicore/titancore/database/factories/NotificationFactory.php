<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'id'              => Str::uuid()->toString(),
            'type'            => 'App\\Notifications\\GenericNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'company_id'      => $user->company_id,
            'data'            => ['message' => $this->faker->sentence()],
        ];
    }
}
