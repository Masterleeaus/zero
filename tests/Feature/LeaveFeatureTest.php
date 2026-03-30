<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work\Leave;
use App\Models\Work\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_leave_creation_is_scoped_to_company(): void
    {
        $user = User::factory()->create(['company_id' => 5]);
        $this->actingAs($user);

        $response = $this->post(route('dashboard.work.leaves.store'), [
            'user_id'    => $user->id,
            'type'       => 'annual',
            'start_date' => now()->format('Y-m-d'),
            'end_date'   => now()->addDay()->format('Y-m-d'),
            'reason'     => 'Rest',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leaves', [
            'user_id'    => $user->id,
            'company_id' => 5,
            'type'       => 'annual',
        ]);

        $this->assertDatabaseCount('leave_histories', 1);
    }

    public function test_insights_include_leave_metrics(): void
    {
        $user = User::factory()->create(['company_id' => 9]);
        $this->actingAs($user);

        Leave::factory()->create([
            'company_id' => $user->company_id,
            'user_id'    => $user->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date'   => now()->addDay()->format('Y-m-d'),
        ]);

        Shift::factory()->create([
            'company_id' => $user->company_id,
            'user_id'    => $user->id,
            'start_at'   => now(),
            'end_at'     => now()->addHours(2),
        ]);

        $response = $this->get(route('dashboard.insights.overview'));
        $response->assertOk();
        $response->assertViewHas('leaveTotals', 1);
        $response->assertViewHas('leaveShiftConflicts', 1);
    }

    public function test_conflicts_with_shift_helper(): void
    {
        $user = User::factory()->create(['company_id' => 21]);
        $leave = Leave::factory()->create([
            'company_id' => $user->company_id,
            'user_id'    => $user->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date'   => now()->addDay()->format('Y-m-d'),
        ]);

        $shift = Shift::factory()->create([
            'company_id' => $user->company_id,
            'user_id'    => $user->id,
            'start_at'   => now(),
            'end_at'     => now()->addHours(4),
        ]);

        $this->assertTrue(Leave::conflictsWithShift($shift));
        $this->assertEquals(1, Leave::conflictsWithShifts($leave->company_id));
    }
}
