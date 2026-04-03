<?php

namespace Database\Factories\Work;

use App\Models\User;
use App\Models\Work\Leave;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        $start = now()->addDays(2)->startOfDay();

        return [
            'company_id' => $user->company_id,
            'user_id'    => $user->id,
            'type'       => 'annual',
            'status'     => 'approved',
            'start_date' => $start,
            'end_date'   => $start->copy()->addDays(1),
            'reason'     => $this->faker->sentence,
        ];
    }
}
