<?php

declare(strict_types=1);

namespace Tests\Feature\Money;

use App\Models\Inventory\Supplier;
use App\Models\Money\FinancialAsset;
use App\Models\Money\Payroll;
use App\Models\Money\PayrollLine;
use App\Models\Money\SupplierBill;
use App\Models\Money\SupplierBillItem;
use App\Models\User;
use App\Models\Work\StaffProfile;
use App\Services\TitanMoney\FinanceReportService;
use App\Services\TitanMoney\PayrollService;
use App\Services\TitanMoney\SupplierBillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceCompletionPassTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Supplier Bills (Accounts Payable)
    // =========================================================================

    public function test_supplier_bill_can_be_created(): void
    {
        $user     = User::factory()->create(['company_id' => 100]);
        $supplier = Supplier::factory()->create(['company_id' => 100]);

        $service = app(SupplierBillService::class);

        $bill = $service->create([
            'company_id'  => 100,
            'supplier_id' => $supplier->id,
            'bill_date'   => now()->toDateString(),
            'due_date'    => now()->addDays(30)->toDateString(),
            'created_by'  => $user->id,
            'items' => [
                ['description' => 'Consulting', 'quantity' => 2, 'unit_price' => 150.00],
            ],
        ]);

        $this->assertDatabaseHas('supplier_bills', [
            'company_id'   => 100,
            'supplier_id'  => $supplier->id,
            'status'       => 'draft',
            'total_amount' => '300.00',
        ]);

        $this->assertEquals('300.00', $bill->total_amount);
    }

    public function test_supplier_bill_can_be_approved(): void
    {
        $user     = User::factory()->create(['company_id' => 101]);
        $supplier = Supplier::factory()->create(['company_id' => 101]);

        $service = app(SupplierBillService::class);

        $bill = $service->create([
            'company_id'  => 101,
            'supplier_id' => $supplier->id,
            'bill_date'   => now()->toDateString(),
            'due_date'    => now()->addDays(30)->toDateString(),
            'created_by'  => $user->id,
            'items' => [
                ['description' => 'Materials', 'quantity' => 1, 'unit_price' => 500.00],
            ],
        ]);

        $service->approve($bill, $user->id);

        $this->assertDatabaseHas('supplier_bills', [
            'id'     => $bill->id,
            'status' => 'approved',
        ]);
    }

    public function test_supplier_bill_payment_marks_bill_paid(): void
    {
        $user     = User::factory()->create(['company_id' => 102]);
        $supplier = Supplier::factory()->create(['company_id' => 102]);

        $service = app(SupplierBillService::class);

        $bill = $service->create([
            'company_id'  => 102,
            'supplier_id' => $supplier->id,
            'bill_date'   => now()->toDateString(),
            'due_date'    => now()->addDays(30)->toDateString(),
            'created_by'  => $user->id,
            'items' => [
                ['description' => 'Invoice', 'quantity' => 1, 'unit_price' => 200.00],
            ],
        ]);

        $service->approve($bill, $user->id);
        $service->recordPayment($bill->fresh(), 200.00);

        $this->assertDatabaseHas('supplier_bills', [
            'id'     => $bill->id,
            'status' => 'paid',
        ]);
    }

    public function test_supplier_bill_index_route_returns_ok(): void
    {
        $user = User::factory()->create(['company_id' => 103]);

        $this->actingAs($user)
            ->get(route('dashboard.money.supplier-bills.index'))
            ->assertOk();
    }

    public function test_supplier_bill_aging_summary_returns_buckets(): void
    {
        $user     = User::factory()->create(['company_id' => 104]);
        $supplier = Supplier::factory()->create(['company_id' => 104]);

        $service = app(SupplierBillService::class);

        // Overdue bill — due 60 days ago
        $bill = $service->create([
            'company_id'  => 104,
            'supplier_id' => $supplier->id,
            'bill_date'   => now()->subDays(65)->toDateString(),
            'due_date'    => now()->subDays(60)->toDateString(),
            'created_by'  => $user->id,
            'items' => [
                ['description' => 'Overdue item', 'quantity' => 1, 'unit_price' => 400.00],
            ],
        ]);

        $service->approve($bill, $user->id);

        $summary = $service->agingSummary(104);

        $this->assertArrayHasKey('31_60', $summary);
        $this->assertEquals(400.00, $summary['31_60']);
    }

    // =========================================================================
    // Payroll
    // =========================================================================

    public function test_payroll_run_can_be_created(): void
    {
        $user = User::factory()->create(['company_id' => 200]);

        $service = app(PayrollService::class);

        $payroll = $service->createRun([
            'company_id'   => 200,
            'period_start' => '2026-04-01',
            'period_end'   => '2026-04-30',
            'pay_date'     => '2026-04-30',
            'created_by'   => $user->id,
        ]);

        $this->assertDatabaseHas('payrolls', [
            'company_id'   => 200,
            'period_start' => '2026-04-01',
            'status'       => 'draft',
        ]);
    }

    public function test_duplicate_payroll_run_for_same_period_is_blocked(): void
    {
        $this->expectException(\RuntimeException::class);

        $user    = User::factory()->create(['company_id' => 201]);
        $service = app(PayrollService::class);

        $payload = [
            'company_id'   => 201,
            'period_start' => '2026-03-01',
            'period_end'   => '2026-03-31',
            'pay_date'     => '2026-03-31',
            'created_by'   => $user->id,
        ];

        $service->createRun($payload);
        $service->createRun($payload); // should throw
    }

    public function test_payroll_line_is_added_from_staff_profile(): void
    {
        $user    = User::factory()->create(['company_id' => 202]);
        $service = app(PayrollService::class);

        $profile = StaffProfile::create([
            'company_id'   => 202,
            'user_id'      => $user->id,
            'hourly_rate'  => 50.00,
            'pay_frequency' => 'weekly',
            'status'       => 'active',
        ]);

        $payroll = $service->createRun([
            'company_id'   => 202,
            'period_start' => '2026-04-01',
            'period_end'   => '2026-04-07',
            'pay_date'     => '2026-04-07',
            'created_by'   => $user->id,
        ]);

        $line = $service->addLine($payroll, [
            'staff_profile_id' => $profile->id,
            'tax_amount'       => 50.00,
            'deductions'       => 0,
        ]);

        $this->assertDatabaseHas('payroll_lines', [
            'payroll_id'       => $payroll->id,
            'staff_profile_id' => $profile->id,
        ]);
    }

    public function test_payroll_can_be_approved(): void
    {
        $user    = User::factory()->create(['company_id' => 203]);
        $service = app(PayrollService::class);

        $profile = StaffProfile::create([
            'company_id'   => 203,
            'user_id'      => $user->id,
            'hourly_rate'  => 40.00,
            'status'       => 'active',
        ]);

        $payroll = $service->createRun([
            'company_id'   => 203,
            'period_start' => '2026-04-01',
            'period_end'   => '2026-04-07',
            'pay_date'     => '2026-04-07',
            'created_by'   => $user->id,
        ]);

        $service->addLine($payroll, ['staff_profile_id' => $profile->id]);

        $service->approve($payroll->fresh(), $user->id);

        $this->assertDatabaseHas('payrolls', [
            'id'     => $payroll->id,
            'status' => 'approved',
        ]);
    }

    public function test_payroll_index_route_returns_ok(): void
    {
        $user = User::factory()->create(['company_id' => 204]);

        $this->actingAs($user)
            ->get(route('dashboard.money.payroll.index'))
            ->assertOk();
    }

    // =========================================================================
    // Financial Assets
    // =========================================================================

    public function test_financial_asset_can_be_registered(): void
    {
        $user = User::factory()->create(['company_id' => 300]);

        $this->actingAs($user)
            ->post(route('dashboard.money.financial-assets.store'), [
                'name'               => 'Company Van',
                'acquisition_date'   => '2025-01-01',
                'acquisition_cost'   => 50000,
                'depreciation_rate'  => 0.2,
                'depreciation_method' => 'straight_line',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('financial_assets', [
            'company_id'      => 300,
            'name'            => 'Company Van',
            'acquisition_cost' => '50000.00',
            'current_value'   => '50000.00',
            'status'          => 'active',
        ]);
    }

    public function test_financial_asset_monthly_depreciation_charge(): void
    {
        $asset = FinancialAsset::create([
            'company_id'       => 301,
            'name'             => 'Laptop',
            'acquisition_date' => '2025-01-01',
            'acquisition_cost' => 1200.00,
            'current_value'    => 1200.00,
            'depreciation_rate' => 0.25,  // 25% annual
            'status'           => 'active',
        ]);

        // Monthly charge = 1200 * 0.25 / 12 = 25.00
        $this->assertEquals(25.00, $asset->monthlyDepreciationCharge());
    }

    public function test_financial_asset_depreciation_reduces_current_value(): void
    {
        $asset = FinancialAsset::create([
            'company_id'       => 302,
            'name'             => 'Printer',
            'acquisition_date' => '2025-01-01',
            'acquisition_cost' => 600.00,
            'current_value'    => 600.00,
            'depreciation_rate' => 0.20,  // 20% annual → 10.00/month
            'status'           => 'active',
        ]);

        $asset->applyDepreciation();

        $this->assertDatabaseHas('financial_assets', [
            'id'            => $asset->id,
            'current_value' => '590.00',
        ]);
    }

    public function test_financial_asset_index_route_returns_ok(): void
    {
        $user = User::factory()->create(['company_id' => 303]);

        $this->actingAs($user)
            ->get(route('dashboard.money.financial-assets.index'))
            ->assertOk();
    }

    // =========================================================================
    // Finance Reports
    // =========================================================================

    public function test_profit_and_loss_report_route_returns_ok(): void
    {
        $user = User::factory()->create(['company_id' => 400]);

        $this->actingAs($user)
            ->get(route('dashboard.money.reports.profit-and-loss'))
            ->assertOk();
    }

    public function test_balance_sheet_report_route_returns_ok(): void
    {
        $user = User::factory()->create(['company_id' => 401]);

        $this->actingAs($user)
            ->get(route('dashboard.money.reports.balance-sheet'))
            ->assertOk();
    }

    public function test_aged_receivables_report_route_returns_ok(): void
    {
        $user = User::factory()->create(['company_id' => 402]);

        $this->actingAs($user)
            ->get(route('dashboard.money.reports.aged-receivables'))
            ->assertOk();
    }

    public function test_aged_payables_report_route_returns_ok(): void
    {
        $user = User::factory()->create(['company_id' => 403]);

        $this->actingAs($user)
            ->get(route('dashboard.money.reports.aged-payables'))
            ->assertOk();
    }

    public function test_finance_report_service_profit_and_loss_returns_correct_structure(): void
    {
        $service = app(FinanceReportService::class);

        $report = $service->profitAndLoss(999, '2026-01-01', '2026-12-31');

        $this->assertArrayHasKey('income', $report);
        $this->assertArrayHasKey('cost_of_goods', $report);
        $this->assertArrayHasKey('gross_profit', $report);
        $this->assertArrayHasKey('expenses', $report);
        $this->assertArrayHasKey('net_profit', $report);
    }

    // =========================================================================
    // Company tenancy isolation
    // =========================================================================

    public function test_supplier_bills_are_scoped_to_company(): void
    {
        $userA    = User::factory()->create(['company_id' => 500]);
        $userB    = User::factory()->create(['company_id' => 501]);
        $supplierA = Supplier::factory()->create(['company_id' => 500]);

        $service = app(SupplierBillService::class);

        $service->create([
            'company_id'  => 500,
            'supplier_id' => $supplierA->id,
            'bill_date'   => now()->toDateString(),
            'due_date'    => now()->addDays(30)->toDateString(),
            'created_by'  => $userA->id,
            'items' => [['description' => 'Company A bill', 'quantity' => 1, 'unit_price' => 100]],
        ]);

        $this->actingAs($userB)
            ->get(route('dashboard.money.supplier-bills.index'))
            ->assertOk()
            ->assertDontSee('Company A bill');
    }
}
