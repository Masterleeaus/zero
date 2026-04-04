<?php

declare(strict_types=1);

namespace Tests\Feature\Money;

use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\JobCostAllocation;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBill;
use App\Models\Money\SupplierBillLine;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\StaffProfile;
use App\Models\Work\TimesheetSubmission;
use App\Policies\JobCostAllocationPolicy;
use App\Services\TitanMoney\JobCostingService;
use App\Services\TitanMoney\LaborCostingService;
use App\Services\TitanMoney\MaterialCostingService;
use App\Services\TitanMoney\PayrollPostingService;
use App\Services\TitanMoney\ProfitabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Finance Domain Pass 3 — Job Costing, Labor, Material, Payroll Posting, Profitability.
 *
 * Validates:
 *   - Expense normalization fields (cost_bucket, reimbursable_to_customer, allocation_reference)
 *   - JobCostAllocation creation and company tenancy scope
 *   - Labor cost calculation from TimesheetSubmission + StaffProfile
 *   - Profitability calculation against job cost allocations and invoices
 *   - Payroll posting payload structure
 *   - Reimbursable expense flag behavior
 *   - JobCostAllocationPolicy company enforcement
 *   - Material cost allocation from supplier bill lines
 */
class FinancePass3Test extends TestCase
{
    use RefreshDatabase;

    private int $companyId = 42;

    private function makeUser(int $companyId = 0, string $role = 'admin'): User
    {
        return User::factory()->create([
            'company_id' => $companyId ?: $this->companyId,
            'role'       => $role,
        ]);
    }

    private function makeServiceJob(int $companyId = 0): ServiceJob
    {
        return ServiceJob::withoutGlobalScope('company')->create([
            'company_id' => $companyId ?: $this->companyId,
            'title'      => 'Test Job',
            'status'     => 'scheduled',
        ]);
    }

    // -----------------------------------------------------------------------
    // Stage 1 — Expense normalization
    // -----------------------------------------------------------------------

    public function test_expense_can_be_normalized_with_cost_bucket(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        Expense::create([
            'company_id'              => $this->companyId,
            'title'                   => 'Materials purchase',
            'amount'                  => 150.00,
            'expense_date'            => now()->toDateString(),
            'cost_bucket'             => 'materials',
            'service_job_id'          => null,
            'reimbursable_to_customer' => false,
            'status'                  => 'pending',
            'created_by'              => $user->id,
        ]);

        $this->assertDatabaseHas('expenses', [
            'company_id'              => $this->companyId,
            'cost_bucket'             => 'materials',
            'reimbursable_to_customer' => false,
        ]);
    }

    public function test_expense_normalization_fields_can_be_set(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        Expense::create([
            'company_id'              => $this->companyId,
            'title'                   => 'Overhead allocation',
            'amount'                  => 75.00,
            'expense_date'            => now()->toDateString(),
            'cost_bucket'             => 'overhead',
            'reimbursable_to_customer' => true,
            'allocation_reference'    => 'REF-001',
            'status'                  => 'pending',
            'created_by'              => $user->id,
        ]);

        $this->assertDatabaseHas('expenses', [
            'company_id'              => $this->companyId,
            'cost_bucket'             => 'overhead',
            'reimbursable_to_customer' => true,
            'allocation_reference'    => 'REF-001',
        ]);
    }

    // -----------------------------------------------------------------------
    // Stage 2 — Job cost allocation
    // -----------------------------------------------------------------------

    public function test_job_cost_allocation_can_be_created_manually(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $job = $this->makeServiceJob();

        $service    = app(JobCostingService::class);
        $allocation = $service->allocateManual(
            [
                'service_job_id' => $job->id,
                'cost_type'      => 'labour',
                'source_type'    => 'manual_adjustment',
                'amount'         => 500.00,
                'allocated_at'   => now()->toDateString(),
            ],
            $this->companyId,
            $user->id
        );

        $this->assertDatabaseHas('job_cost_allocations', [
            'company_id'     => $this->companyId,
            'service_job_id' => $job->id,
            'cost_type'      => 'labour',
            'amount'         => '500.00',
        ]);

        $this->assertEquals('labour', $allocation->cost_type);
        $this->assertEquals($this->companyId, $allocation->company_id);
    }

    public function test_job_cost_allocation_scoped_to_company(): void
    {
        $userA = $this->makeUser(companyId: 42);
        $userB = $this->makeUser(companyId: 99);

        // Create allocation for company A while acting as user A
        $this->actingAs($userA);
        $jobA = $this->makeServiceJob(42);
        JobCostAllocation::create([
            'company_id'     => 42,
            'service_job_id' => $jobA->id,
            'cost_type'      => 'labour',
            'source_type'    => 'manual_adjustment',
            'amount'         => 250.00,
            'allocated_at'   => now()->toDateString(),
        ]);

        // Acting as user B — the global scope should filter to company 99 only
        $this->actingAs($userB);
        $allocations = JobCostAllocation::all();

        $this->assertTrue($allocations->every(fn ($a) => $a->company_id === 99));
    }

    // -----------------------------------------------------------------------
    // Stage 3 — Labor costing
    // -----------------------------------------------------------------------

    public function test_labor_cost_calculation_from_timesheet(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        StaffProfile::create([
            'company_id'  => $this->companyId,
            'user_id'     => $user->id,
            'hourly_rate' => 25.00,
            'status'      => 'active',
        ]);

        $submission = TimesheetSubmission::create([
            'company_id'  => $this->companyId,
            'user_id'     => $user->id,
            'week_start'  => now()->startOfWeek()->toDateString(),
            'total_hours' => 8,
            'status'      => 'approved',
        ]);

        $service = app(LaborCostingService::class);
        $result  = $service->costForTimesheetSubmission($submission);

        $this->assertEquals(8.0, $result['hours']);
        $this->assertEquals(25.00, $result['rate']);
        $this->assertEquals(200.00, $result['cost']);
    }

    // -----------------------------------------------------------------------
    // Stage 4 — Profitability
    // -----------------------------------------------------------------------

    public function test_profitability_calculation_for_job(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $job = $this->makeServiceJob();

        // Create cost allocations totalling 800
        JobCostAllocation::create([
            'company_id'     => $this->companyId,
            'service_job_id' => $job->id,
            'cost_type'      => 'labour',
            'source_type'    => 'manual_adjustment',
            'amount'         => 500.00,
            'allocated_at'   => now()->toDateString(),
        ]);
        JobCostAllocation::create([
            'company_id'     => $this->companyId,
            'service_job_id' => $job->id,
            'cost_type'      => 'material',
            'source_type'    => 'manual_adjustment',
            'amount'         => 300.00,
            'allocated_at'   => now()->toDateString(),
        ]);

        // Create a paid invoice linked to the job (revenue = 1200)
        Invoice::withoutGlobalScope('company')->create([
            'company_id'     => $this->companyId,
            'service_job_id' => $job->id,
            'total'          => 1200.00,
            'status'         => 'paid',
            'created_by'     => $user->id,
        ]);

        $service = app(ProfitabilityService::class);
        $result  = $service->forJob($job);

        $this->assertEquals(800.00, $result['gross_cost']);
        $this->assertEquals(1200.00, $result['gross_revenue']);
        $this->assertEquals(400.00, $result['gross_margin']);
        $this->assertArrayHasKey('margin_pct', $result);
    }

    // -----------------------------------------------------------------------
    // Stage 5 — Payroll posting payload
    // -----------------------------------------------------------------------

    public function test_payroll_posting_payload_is_generated(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $payroll = Payroll::create([
            'company_id'   => $this->companyId,
            'period_start' => '2026-04-01',
            'period_end'   => '2026-04-30',
            'pay_date'     => '2026-04-30',
            'total_gross'  => 5000.00,
            'total_tax'    => 500.00,
            'total_net'    => 4500.00,
            'status'       => 'approved',
            'created_by'   => $user->id,
        ]);

        $service = app(PayrollPostingService::class);
        $payload = $service->buildPostingPayload($payroll);

        $this->assertArrayHasKey('lines', $payload);

        $lines = collect($payload['lines']);

        $wagesLine = $lines->firstWhere('account_code', 'WAGES_EXPENSE');
        $this->assertNotNull($wagesLine, 'Wages expense line should exist.');
        $this->assertEquals(5000.00, $wagesLine['amount']);
        $this->assertEquals('debit', $wagesLine['type']);

        $payableLine = $lines->firstWhere('account_code', 'PAYROLL_PAYABLE');
        $this->assertNotNull($payableLine, 'Payroll payable line should exist.');
        $this->assertEquals(4500.00, $payableLine['amount']);

        $taxLine = $lines->firstWhere('account_code', 'TAX_WITHHOLDING_PAYABLE');
        $this->assertNotNull($taxLine, 'Tax withholding line should exist.');
        $this->assertEquals(500.00, $taxLine['amount']);
    }

    // -----------------------------------------------------------------------
    // Stage 6 — Reimbursable expense flag
    // -----------------------------------------------------------------------

    public function test_reimbursable_expense_flag_behavior(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $job = $this->makeServiceJob();

        $expense = Expense::create([
            'company_id'              => $this->companyId,
            'title'                   => 'Client site materials',
            'amount'                  => 120.00,
            'expense_date'            => now()->toDateString(),
            'cost_bucket'             => 'reimbursable',
            'service_job_id'          => $job->id,
            'reimbursable_to_customer' => true,
            'status'                  => 'approved',
            'created_by'              => $user->id,
        ]);

        $service    = app(JobCostingService::class);
        $allocation = $service->allocateExpense($expense);

        $this->assertEquals('expense', $allocation->source_type);
        $this->assertEquals('reimbursable', $allocation->cost_type);

        $this->assertDatabaseHas('expenses', [
            'id'                      => $expense->id,
            'reimbursable_to_customer' => true,
        ]);
    }

    // -----------------------------------------------------------------------
    // Stage 7 — Policy company scope enforcement
    // -----------------------------------------------------------------------

    public function test_job_cost_allocation_policy_enforces_company_scope(): void
    {
        $userA = $this->makeUser(companyId: 42, role: 'admin');

        // Create allocation for company B directly, bypassing global scope
        $allocationB = JobCostAllocation::withoutGlobalScope('company')->create([
            'company_id'  => 99,
            'cost_type'   => 'labour',
            'source_type' => 'manual_adjustment',
            'amount'      => 300.00,
            'allocated_at' => now()->toDateString(),
        ]);

        $policy = new JobCostAllocationPolicy();

        $this->assertFalse(
            $policy->view($userA, $allocationB),
            'User from company A should not be able to view company B allocation.'
        );
    }

    // -----------------------------------------------------------------------
    // Stage 8 — Material cost allocation
    // -----------------------------------------------------------------------

    public function test_material_cost_can_be_allocated_to_job(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $job = $this->makeServiceJob();

        $materialService = app(MaterialCostingService::class);

        $allocation = $materialService->allocateInventoryUsage(
            [
                'cost_type'   => 'material',
                'description' => 'Copper piping 10m',
                'amount'      => 180.00,
                'quantity'    => 10.0,
                'unit_cost'   => 18.00,
                'allocated_at' => now()->toDateString(),
            ],
            $job->id,
            $this->companyId,
            $user->id
        );

        $this->assertDatabaseHas('job_cost_allocations', [
            'company_id'     => $this->companyId,
            'service_job_id' => $job->id,
            'cost_type'      => 'material',
            'source_type'    => 'inventory_usage',
            'amount'         => '180.00',
        ]);

        $this->assertEquals('material', $allocation->cost_type);
    }
}
