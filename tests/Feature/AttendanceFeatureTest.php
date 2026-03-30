<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_scoped_to_company(): void
    {
        $userA = User::factory()->create(['company_id' => 10]);
        $userB = User::factory()->create(['company_id' => 11]);

        Attendance::factory()->create(['company_id' => 10, 'user_id' => $userA->id]);
        Attendance::factory()->create(['company_id' => 11, 'user_id' => $userB->id]);

        $response = $this->actingAs($userA)->get(route('dashboard.work.attendance.index'));

        $response->assertStatus(200);
        $this->assertStringContainsString('attendance', strtolower($response->getContent()));
        $this->assertEquals(
            1,
            $response->viewData('attendances')->count()
        );
    }
}
