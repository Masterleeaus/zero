<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_assign_job_to_shift(): void
    {
        $user = User::factory()->create();
        $job = ServiceJob::factory([
            'company_id' => $user->company_id,
            'site_id' => null,
        ])->create();

        $shift = Shift::factory()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'service_job_id' => null,
        ]);

        $response = $this->actingAs($user)->postJson(
            route('dashboard.work.shifts.assign', $shift),
            ['service_job_id' => $job->id]
        );

        $response->assertOk();
        $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'service_job_id' => $job->id,
            'status' => 'assigned',
        ]);
    }
}
