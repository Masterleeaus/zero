<?php

namespace Database\Factories\Work;

use App\Models\User;
use App\Models\Work\Attendance;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $start = now()->subHour();
        $user = User::factory()->create();

        return [
            'company_id'     => $user->company_id,
            'user_id'        => $user->id,
            'service_job_id' => ServiceJob::factory(['company_id' => $user->company_id]),
            'check_in_at'    => $start,
            'check_out_at'   => $start->clone()->addMinutes(30),
            'status'         => 'closed',
        ];
    }
}
