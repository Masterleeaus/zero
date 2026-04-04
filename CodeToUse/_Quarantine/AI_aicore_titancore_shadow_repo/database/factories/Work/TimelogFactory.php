<?php

namespace Database\Factories\Work;

use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\Timelog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Timelog>
 */
class TimelogFactory extends Factory
{
    protected $model = Timelog::class;

    public function definition(): array
    {
        $start = now()->subHours(2);

        return [
            'company_id'     => 1,
            'user_id'        => User::factory(['company_id' => 1]),
            'service_job_id' => ServiceJob::factory(['company_id' => 1]),
            'started_at'     => $start,
            'ended_at'       => $start->clone()->addHour(),
            'notes'          => $this->faker->sentence(),
        ];
    }
}
