<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work\Attendance;
use App\Models\Work\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_missed_attendance_is_created_for_overdue_shift(): void
    {
        $user = User::factory()->create();
        $shift = Shift::factory()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'start_at' => now()->subHours(2),
            'end_at' => now()->subHour(),
            'service_job_id' => null,
        ]);

        Attendance::markMissedForShift($shift);

        $this->assertDatabaseHas('attendances', [
            'company_id' => $user->company_id,
            'shift_id' => $shift->id,
            'status' => 'missed',
        ]);
    }
}
