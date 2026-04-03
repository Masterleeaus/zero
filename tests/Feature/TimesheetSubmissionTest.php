<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work\WeeklyTimesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_timesheet_lists_for_company(): void
    {
        $user = User::factory()->create(['company_id' => 10]);
        $other = User::factory()->create(['company_id' => 11]);

        WeeklyTimesheet::query()->withoutGlobalScope('company')->insert([
            'company_id'  => 10,
            'user_id'     => $user->id,
            'week_start'  => now()->startOfWeek()->toDateString(),
            'week_end'    => now()->endOfWeek()->toDateString(),
            'total_hours' => 40,
            'status'      => 'pending',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        WeeklyTimesheet::query()->withoutGlobalScope('company')->insert([
            'company_id'  => 11,
            'user_id'     => $other->id,
            'week_start'  => now()->startOfWeek()->toDateString(),
            'week_end'    => now()->endOfWeek()->toDateString(),
            'total_hours' => 32,
            'status'      => 'pending',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.team.timesheets.index'));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('timesheets')->count());
    }

    public function test_timesheet_submit_changes_status(): void
    {
        $user = User::factory()->create(['company_id' => 20]);
        $this->actingAs($user);

        $timesheet = WeeklyTimesheet::query()->withoutGlobalScope('company')->create([
            'company_id'  => 20,
            'user_id'     => $user->id,
            'week_start'  => now()->startOfWeek()->toDateString(),
            'week_end'    => now()->endOfWeek()->toDateString(),
            'total_hours' => 0,
            'status'      => 'pending',
        ]);

        $response = $this->post(route('dashboard.team.timesheets.submit', $timesheet));

        $response->assertRedirect();

        $this->assertDatabaseHas('weekly_timesheets', [
            'id'     => $timesheet->id,
            'status' => 'submitted',
        ]);
    }

    public function test_timesheet_approve_restricted_to_managers(): void
    {
        $user = User::factory()->create([
            'company_id' => 30,
            'type'       => 'user',
        ]);
        $this->actingAs($user);

        $timesheet = WeeklyTimesheet::query()->withoutGlobalScope('company')->create([
            'company_id'  => 30,
            'user_id'     => $user->id,
            'week_start'  => now()->startOfWeek()->toDateString(),
            'week_end'    => now()->endOfWeek()->toDateString(),
            'total_hours' => 40,
            'status'      => 'submitted',
        ]);

        $response = $this->post(route('dashboard.team.timesheets.approve', $timesheet));

        $response->assertForbidden();

        $this->assertDatabaseHas('weekly_timesheets', [
            'id'     => $timesheet->id,
            'status' => 'submitted',
        ]);
    }
}
