<?php

declare(strict_types=1);

namespace Tests\Feature\HRM;

use App\Models\User;
use App\Models\Work\Attendance;
use App\Models\Work\Department;
use App\Models\Work\EmploymentLifecycleState;
use App\Models\Work\Leave;
use App\Models\Work\ShiftAssignment;
use App\Models\Work\StaffProfile;
use App\Services\HRM\BiometricIngestService;
use App\Services\HRM\PayrollInputService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrmPass2Test extends TestCase
{
    use RefreshDatabase;

    private function adminUser(int $companyId = 1): User
    {
        return User::factory()->create([
            'company_id' => $companyId,
            'type'       => 'admin',
        ]);
    }

    private function regularUser(int $companyId = 1): User
    {
        return User::factory()->create(['company_id' => $companyId]);
    }

    // ── Department ──────────────────────────────────────────────────────────

    public function test_department_creation_scoped_to_company(): void
    {
        $user = $this->adminUser(10);

        $department = Department::withoutGlobalScopes()->create([
            'company_id' => 10,
            'name'       => 'Engineering',
            'status'     => 'active',
        ]);

        $this->assertDatabaseHas('departments', [
            'company_id' => 10,
            'name'       => 'Engineering',
        ]);

        $this->assertSame(10, $department->company_id);
    }

    public function test_department_hierarchy_parent_child(): void
    {
        $parent = Department::withoutGlobalScopes()->create([
            'company_id' => 5,
            'name'       => 'Operations',
            'status'     => 'active',
        ]);

        $child = Department::withoutGlobalScopes()->create([
            'company_id' => 5,
            'name'       => 'Dispatch',
            'parent_id'  => $parent->id,
            'status'     => 'active',
        ]);

        $this->assertSame($parent->id, $child->parent_id);
        $this->assertSame(1, Department::withoutGlobalScopes()->where('parent_id', $parent->id)->count());
    }

    public function test_staff_profile_linked_to_department(): void
    {
        $user = $this->regularUser(7);

        $department = Department::withoutGlobalScopes()->create([
            'company_id' => 7,
            'name'       => 'HR',
            'status'     => 'active',
        ]);

        $profile = StaffProfile::withoutGlobalScopes()->create([
            'company_id'   => 7,
            'user_id'      => $user->id,
            'department_id' => $department->id,
            'job_title'    => 'HR Manager',
        ]);

        $this->assertDatabaseHas('staff_profiles', [
            'user_id'       => $user->id,
            'department_id' => $department->id,
        ]);
    }

    public function test_direct_reports_via_staff_profile(): void
    {
        $manager  = $this->regularUser(8);
        $employee = $this->regularUser(8);

        StaffProfile::withoutGlobalScopes()->create([
            'company_id' => 8,
            'user_id'    => $employee->id,
            'manager_id' => $manager->id,
            'job_title'  => 'Developer',
        ]);

        $reports = StaffProfile::withoutGlobalScopes()
            ->where('manager_id', $manager->id)
            ->get();

        $this->assertCount(1, $reports);
        $this->assertSame($employee->id, $reports->first()->user_id);
    }

    // ── Shift Assignment ────────────────────────────────────────────────────

    public function test_shift_assignment_creates_record(): void
    {
        $user  = $this->regularUser(9);
        $admin = $this->adminUser(9);

        $shift = \App\Models\Work\Shift::withoutGlobalScopes()->create([
            'company_id' => 9,
            'user_id'    => $user->id,
            'start_at'   => now(),
            'end_at'     => now()->addHours(8),
            'status'     => 'draft',
        ]);

        $assignment = ShiftAssignment::withoutGlobalScopes()->create([
            'company_id'  => 9,
            'shift_id'    => $shift->id,
            'user_id'     => $user->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status'      => 'assigned',
        ]);

        $this->assertDatabaseHas('shift_assignments', [
            'shift_id'    => $shift->id,
            'user_id'     => $user->id,
            'assigned_by' => $admin->id,
        ]);
    }

    // ── Employment Lifecycle ────────────────────────────────────────────────

    public function test_employment_status_change_creates_lifecycle_state(): void
    {
        $user    = $this->regularUser(11);
        $changer = $this->adminUser(11);

        $profile = StaffProfile::withoutGlobalScopes()->create([
            'company_id' => 11,
            'user_id'    => $user->id,
            'job_title'  => 'Technician',
        ]);

        $state = EmploymentLifecycleState::withoutGlobalScopes()->create([
            'company_id'       => 11,
            'user_id'          => $user->id,
            'staff_profile_id' => $profile->id,
            'status'           => 'terminated',
            'previous_status'  => 'active',
            'changed_by'       => $changer->id,
            'effective_at'     => now(),
        ]);

        $this->assertDatabaseHas('employment_lifecycle_states', [
            'user_id'         => $user->id,
            'status'          => 'terminated',
            'previous_status' => 'active',
        ]);
    }

    // ── Leave Approval ──────────────────────────────────────────────────────

    public function test_leave_approve_changes_status(): void
    {
        $admin = $this->adminUser(12);

        $leave = Leave::withoutGlobalScopes()->create([
            'company_id' => 12,
            'user_id'    => $admin->id,
            'type'       => 'annual',
            'status'     => 'pending',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addDay()->toDateString(),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('dashboard.team.work.leaves.approve', $leave));

        $response->assertRedirect();
        $this->assertDatabaseHas('leaves', [
            'id'          => $leave->id,
            'status'      => 'approved',
            'approved_by' => $admin->id,
        ]);
    }

    public function test_leave_reject_stores_reason(): void
    {
        $admin = $this->adminUser(13);

        $leave = Leave::withoutGlobalScopes()->create([
            'company_id' => 13,
            'user_id'    => $admin->id,
            'type'       => 'sick',
            'status'     => 'pending',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addDay()->toDateString(),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('dashboard.team.work.leaves.reject', $leave), [
                'reason' => 'Insufficient notice',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leaves', [
            'id'               => $leave->id,
            'status'           => 'rejected',
            'rejection_reason' => 'Insufficient notice',
        ]);
    }

    // ── PayrollInputService ─────────────────────────────────────────────────

    public function test_payroll_input_service_calculates_hours(): void
    {
        $user = $this->regularUser(14);

        Attendance::withoutGlobalScopes()->create([
            'company_id'       => 14,
            'user_id'          => $user->id,
            'check_in_at'      => '2025-01-06 08:00:00',
            'check_out_at'     => '2025-01-06 16:00:00',
            'duration_minutes' => 480,
            'status'           => 'checked_out',
        ]);

        $service = new PayrollInputService();
        $hours   = $service->calculateWeeklyHours($user->id, '2025-01-06', '2025-01-12');

        $this->assertSame(8.0, $hours);
    }

    // ── BiometricIngestService ──────────────────────────────────────────────

    public function test_biometric_punch_ingested(): void
    {
        $user = $this->regularUser(15);

        $service = new BiometricIngestService();

        $punch = $service->ingestPunch([
            'company_id'   => 15,
            'user_id'      => $user->id,
            'punch_type'   => 'clock_in',
            'punch_source' => 'mobile',
            'punched_at'   => now(),
        ]);

        $this->assertDatabaseHas('biometric_punches', [
            'user_id'    => $user->id,
            'punch_type' => 'clock_in',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => 'checked_in',
        ]);
    }
}
