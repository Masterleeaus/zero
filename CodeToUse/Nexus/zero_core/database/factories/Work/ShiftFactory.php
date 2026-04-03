<?php

namespace Database\Factories\Work;

use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'company_id'    => $user->company_id,
            'user_id'       => $user->id,
            'service_job_id'=> ServiceJob::factory(['company_id' => $user->company_id]),
            'start_at'      => now()->addDay(),
            'end_at'        => now()->addDay()->addHours(2),
            'status'        => 'scheduled',
        ];
    }
}
