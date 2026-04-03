<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work\StaffProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_profile_creation_scoped_to_company(): void
    {
        $user = User::factory()->create(['company_id' => 50]);
        $this->actingAs($user);

        $response = $this->post(route('dashboard.team.staff-profiles.store'), [
            'user_id'         => $user->id,
            'job_title'       => 'Field Technician',
            'department'      => 'Operations',
            'employment_type' => 'full_time',
            'start_date'      => now()->toDateString(),
            'status'          => 'active',
        ]);

        $response->assertRedirect(route('dashboard.team.staff-profiles.index'));

        $this->assertDatabaseHas('staff_profiles', [
            'user_id'         => $user->id,
            'company_id'      => 50,
            'job_title'       => 'Field Technician',
            'employment_type' => 'full_time',
        ]);
    }

    public function test_staff_profile_visible_to_company_users(): void
    {
        $userA = User::factory()->create(['company_id' => 60]);
        $userB = User::factory()->create(['company_id' => 61]);

        StaffProfile::query()->withoutGlobalScope('company')->create([
            'company_id' => 60,
            'user_id'    => $userA->id,
            'job_title'  => 'Cleaner',
            'status'     => 'active',
        ]);

        StaffProfile::query()->withoutGlobalScope('company')->create([
            'company_id' => 61,
            'user_id'    => $userB->id,
            'job_title'  => 'Supervisor',
            'status'     => 'active',
        ]);

        $response = $this->actingAs($userA)->get(route('dashboard.team.staff-profiles.index'));

        $response->assertOk();

        $this->assertDatabaseCount('staff_profiles', 2);
        $this->assertEquals(
            1,
            StaffProfile::query()->where('company_id', $userA->company_id)->count(),
        );
    }
}
