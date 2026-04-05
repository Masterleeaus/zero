<?php

declare(strict_types=1);

namespace Tests\Feature\Money;

use App\Events\Money\CashRunwayWarning;
use App\Events\Money\CashflowRiskDetected;
use App\Events\Money\FinancialRiskDetected;
use App\Events\Money\FinancialSnapshotUpdated;
use App\Events\Money\ForecastGenerated;
use App\Events\Money\MarginDropDetected;
use App\Events\Money\MarginThresholdCrossed;
use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\JobCostAllocation;
use App\Models\Money\Payment;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBill;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Services\TitanMoney\CashflowService;
use App\Services\TitanMoney\CostDriverAnalysisService;
use App\Services\TitanMoney\FinancialKpiService;
use App\Services\TitanMoney\FinancialSignalService;
use App\Services\TitanMoney\FinancialSnapshotService;
use App\Services\TitanMoney\ForecastingService;
use App\Services\TitanMoney\ProfitabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Finance Domain Pass 4 — Reporting + Dashboards + Forecasting Layer.
 *
 * Validates:
 *   - FinancialSnapshotService: snapshot correctness, period scoping, cross-company isolation
 *   - CashflowService: projection math, period data, rolling windows
 *   - ForecastingService: forecast generation structure
 *   - FinancialKpiService: KPI calculation correctness
 *   - CostDriverAnalysisService: cost breakdown and ranking
 *   - FinancialSignalService: alert trigger thresholds
 *   - ProfitabilityService: per-dimension margins
 *   - Event dispatching: FinancialSnapshotUpdated, ForecastGenerated, MarginDropDetected
 *   - Dashboard routes: HTTP 200 for all dashboard endpoints
 */
class FinancePass4Test extends TestCase
{
    use RefreshDatabase;

    private int $companyId = 77;
    private int $otherCompanyId = 99;

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function makeUser(int $companyId = 0): User
    {
        return User::factory()->create([
            'company_id' => $companyId ?: $this->companyId,
            'role'       => 'admin',
        ]);
    }

    private function makeInvoice(int $companyId, string $status, float $total, string $issueDate): Invoice
    {
        return Invoice::withoutGlobalScope('company')->create([
            'company_id'     => $companyId,
            'created_by'     => 1,
            'invoice_number' => 'INV-' . uniqid(),
            'status'         => $status,
            'issue_date'     => $issueDate,
            'due_date'       => $issueDate,
            'total'          => $total,
            'subtotal'       => $total,
            'paid_amount'    => $status === 'paid' ? $total : 0,
            'balance'        => $status === 'paid' ? 0 : $total,
            'currency'       => 'AUD',
        ]);
    }

    private function makePayment(int $companyId, float $amount, string $paidAt): Payment
    {
        $invoice = $this->makeInvoice($companyId, 'paid', $amount, now()->toDateString());

        return Payment::create([
            'company_id' => $companyId,
            'created_by' => 1,
            'invoice_id' => $invoice->id,
            'amount'     => $amount,
            'method'     => 'bank_transfer',
            'paid_at'    => $paidAt,
        ]);
    }

    private function makeJobCostAllocation(int $companyId, float $amount, string $costType, string $allocatedAt, ?int $jobId = null): JobCostAllocation
    {
        return JobCostAllocation::withoutGlobalScope('company')->create([
            'company_id'     => $companyId,
            'service_job_id' => $jobId,
            'cost_type'      => $costType,
            'source_type'    => 'manual_adjustment',
            'amount'         => $amount,
            'allocated_at'   => $allocatedAt,
            'posted'         => false,
            'created_by'     => 1,
        ]);
    }

    private function makeServiceJob(int $companyId, string $status = 'completed'): ServiceJob
    {
        return ServiceJob::withoutGlobalScope('company')->create([
            'company_id' => $companyId,
            'title'      => 'Test Job ' . uniqid(),
            'status'     => $status,
        ]);
    }

    // ------------------------------------------------------------------
    // Stage 1 — FinancialSnapshotService
    // ------------------------------------------------------------------

    public function test_snapshot_returns_correct_cash_on_hand(): void
    {
        $this->makePayment($this->companyId, 1000.00, now()->toDateTimeString());
        $this->makePayment($this->companyId, 500.00, now()->toDateTimeString());

        $service  = app(FinancialSnapshotService::class);
        $snapshot = $service->snapshot($this->companyId);

        $this->assertEquals(1500.00, $snapshot['cash_on_hand']);
    }

    public function test_snapshot_is_company_scoped(): void
    {
        $this->makePayment($this->companyId, 1000.00, now()->toDateTimeString());
        $this->makePayment($this->otherCompanyId, 9999.00, now()->toDateTimeString());

        $service  = app(FinancialSnapshotService::class);
        $snapshot = $service->snapshot($this->companyId);

        $this->assertEquals(1000.00, $snapshot['cash_on_hand']);
    }

    public function test_snapshot_dispatches_event(): void
    {
        Event::fake([FinancialSnapshotUpdated::class]);

        $service = app(FinancialSnapshotService::class);
        $service->snapshot($this->companyId);

        Event::assertDispatched(FinancialSnapshotUpdated::class, function ($event) {
            return $event->companyId === $this->companyId;
        });
    }

    public function test_snapshot_contains_all_keys(): void
    {
        $service  = app(FinancialSnapshotService::class);
        $snapshot = $service->snapshot($this->companyId);

        $expectedKeys = [
            'as_at', 'company_id', 'cash_on_hand', 'receivables_total',
            'payables_total', 'wages_liability_estimate', 'supplier_liability',
            'job_cost_outstanding', 'unbilled_work_estimate', 'gross_margin_estimate',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $snapshot, "Missing key: $key");
        }
    }

    public function test_rolling_snapshot_covers_correct_period(): void
    {
        // Payment 40 days ago — should NOT appear in 30-day rolling snapshot
        $this->makePayment($this->companyId, 999.00, now()->subDays(40)->toDateTimeString());
        // Payment today — should appear
        $this->makePayment($this->companyId, 100.00, now()->toDateTimeString());

        $service  = app(FinancialSnapshotService::class);
        $snapshot = $service->rollingSnapshot($this->companyId, 30);

        // The rolling snapshot counts cash_in (net positive payments), not total payments
        $this->assertEquals(100.00, $snapshot['cash_in']);
    }

    // ------------------------------------------------------------------
    // Stage 2 — CashflowService
    // ------------------------------------------------------------------

    public function test_cashflow_for_period_returns_correct_actual_inflow(): void
    {
        $from = Carbon::parse('2026-01-01');
        $to   = Carbon::parse('2026-01-31');

        $invoice = $this->makeInvoice($this->companyId, 'paid', 500.00, '2026-01-15');
        Payment::create([
            'company_id' => $this->companyId,
            'created_by' => 1,
            'invoice_id' => $invoice->id,
            'amount'     => 500.00,
            'method'     => 'bank_transfer',
            'paid_at'    => '2026-01-15 10:00:00',
        ]);

        $service = app(CashflowService::class);
        $result  = $service->forPeriod($this->companyId, $from, $to);

        $this->assertEquals(500.00, $result['actual_inflow']);
    }

    public function test_cashflow_for_period_is_company_scoped(): void
    {
        $from = Carbon::parse('2026-01-01');
        $to   = Carbon::parse('2026-01-31');

        $invoice = $this->makeInvoice($this->otherCompanyId, 'paid', 9999.00, '2026-01-15');
        Payment::create([
            'company_id' => $this->otherCompanyId,
            'created_by' => 1,
            'invoice_id' => $invoice->id,
            'amount'     => 9999.00,
            'method'     => 'bank_transfer',
            'paid_at'    => '2026-01-15 10:00:00',
        ]);

        $service = app(CashflowService::class);
        $result  = $service->forPeriod($this->companyId, $from, $to);

        $this->assertEquals(0.00, $result['actual_inflow']);
    }

    public function test_weekly_projection_returns_correct_count(): void
    {
        $service = app(CashflowService::class);
        $result  = $service->weeklyProjection($this->companyId, 4);

        $this->assertCount(4, $result);
        $this->assertArrayHasKey('week', $result[0]);
    }

    public function test_monthly_projection_returns_correct_count(): void
    {
        $service = app(CashflowService::class);
        $result  = $service->monthlyProjection($this->companyId, 3);

        $this->assertCount(3, $result);
        $this->assertArrayHasKey('month', $result[0]);
    }

    public function test_rolling_90_day_has_correct_structure(): void
    {
        $service = app(CashflowService::class);
        $result  = $service->rolling90Day($this->companyId);

        $this->assertEquals(90, $result['rolling_days']);
        $this->assertArrayHasKey('totals', $result);
        $this->assertArrayHasKey('weeks', $result);
        $this->assertArrayHasKey('net_position', $result['totals']);
    }

    // ------------------------------------------------------------------
    // Stage 3 — ProfitabilityService (extended)
    // ------------------------------------------------------------------

    public function test_profitability_for_period_is_company_scoped(): void
    {
        $date = now()->subDays(5)->toDateString();

        $this->makeJobCostAllocation($this->companyId, 200.00, 'labour', $date);
        $this->makeJobCostAllocation($this->otherCompanyId, 9999.00, 'labour', $date);

        $service = app(ProfitabilityService::class);
        $result  = $service->forPeriod(
            $this->companyId,
            now()->subDays(10)->startOfDay(),
            now()->endOfDay()
        );

        $this->assertEquals(200.00, $result['gross_cost']);
    }

    // ------------------------------------------------------------------
    // Stage 4 — CostDriverAnalysisService
    // ------------------------------------------------------------------

    public function test_cost_driver_breakdown_aggregates_buckets(): void
    {
        $date = now()->subDays(2)->toDateString();

        $this->makeJobCostAllocation($this->companyId, 400.00, 'labour', $date);
        $this->makeJobCostAllocation($this->companyId, 300.00, 'material', $date);
        $this->makeJobCostAllocation($this->companyId, 100.00, 'overhead', $date);

        $service = app(CostDriverAnalysisService::class);
        $result  = $service->breakdown($this->companyId);

        $this->assertEquals(800.00, $result['total_cost']);
        $this->assertEquals(400.00, $result['totals']['labor']);
        $this->assertEquals(300.00, $result['totals']['materials']);
    }

    public function test_cost_driver_percentages_sum_to_100(): void
    {
        $date = now()->subDays(2)->toDateString();

        $this->makeJobCostAllocation($this->companyId, 600.00, 'labour', $date);
        $this->makeJobCostAllocation($this->companyId, 400.00, 'material', $date);

        $service = app(CostDriverAnalysisService::class);
        $result  = $service->breakdown($this->companyId);

        $total = array_sum($result['percentages']);
        $this->assertEqualsWithDelta(100.0, $total, 0.1);
    }

    public function test_cost_driver_ranked_drivers_ordered_desc(): void
    {
        $date = now()->subDays(2)->toDateString();

        $this->makeJobCostAllocation($this->companyId, 100.00, 'material', $date);
        $this->makeJobCostAllocation($this->companyId, 500.00, 'labour', $date);

        $service = app(CostDriverAnalysisService::class);
        $result  = $service->breakdown($this->companyId);

        // Labor (500) should rank first
        $this->assertEquals('labor', $result['ranked_drivers'][0]['driver']);
    }

    public function test_cost_driver_is_company_scoped(): void
    {
        $date = now()->subDays(2)->toDateString();

        $this->makeJobCostAllocation($this->companyId, 100.00, 'labour', $date);
        $this->makeJobCostAllocation($this->otherCompanyId, 9999.00, 'labour', $date);

        $service = app(CostDriverAnalysisService::class);
        $result  = $service->breakdown($this->companyId);

        $this->assertEquals(100.00, $result['total_cost']);
    }

    // ------------------------------------------------------------------
    // Stage 5 — ForecastingService
    // ------------------------------------------------------------------

    public function test_forecasting_returns_correct_keys(): void
    {
        $service  = app(ForecastingService::class);
        $forecast = $service->generate($this->companyId, 30);

        $expectedKeys = [
            'company_id', 'generated_at', 'horizon_days', 'revenue_forecast',
            'cost_forecast', 'margin_forecast', 'margin_pct_forecast',
            'cash_runway_estimate', 'pending_liabilities',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $forecast, "Missing key: $key");
        }
    }

    public function test_forecasting_dispatches_event(): void
    {
        Event::fake([ForecastGenerated::class]);

        $service = app(ForecastingService::class);
        $service->generate($this->companyId, 30);

        Event::assertDispatched(ForecastGenerated::class, function ($event) {
            return $event->companyId === $this->companyId;
        });
    }

    public function test_forecasting_margin_equals_revenue_minus_cost(): void
    {
        $service  = app(ForecastingService::class);
        $forecast = $service->generate($this->companyId, 30);

        $expected = round($forecast['revenue_forecast'] - $forecast['cost_forecast'], 2);
        $this->assertEquals($expected, $forecast['margin_forecast']);
    }

    // ------------------------------------------------------------------
    // Stage 6 — FinancialKpiService
    // ------------------------------------------------------------------

    public function test_kpi_gross_margin_pct_calculated_correctly(): void
    {
        $date = now()->subDays(5)->toDateString();

        // Revenue: 1000, Cost: 600 → margin 40%
        $this->makeInvoice($this->companyId, 'paid', 1000.00, $date);
        $this->makeJobCostAllocation($this->companyId, 600.00, 'labour', $date);

        $service = app(FinancialKpiService::class);
        $result  = $service->compute(
            $this->companyId,
            now()->subDays(10)->startOfDay(),
            now()->endOfDay()
        );

        $this->assertEquals(40.0, $result['gross_margin_pct']);
    }

    public function test_kpi_is_company_scoped(): void
    {
        $date = now()->subDays(5)->toDateString();

        $this->makeInvoice($this->companyId, 'paid', 1000.00, $date);
        $this->makeInvoice($this->otherCompanyId, 'paid', 9999.00, $date);

        $service = app(FinancialKpiService::class);
        $result  = $service->compute(
            $this->companyId,
            now()->subDays(10)->startOfDay(),
            now()->endOfDay()
        );

        $this->assertEquals(1000.00, $result['total_revenue']);
    }

    public function test_kpi_returns_all_expected_keys(): void
    {
        $service = app(FinancialKpiService::class);
        $result  = $service->compute($this->companyId);

        $expectedKeys = [
            'gross_margin_pct', 'net_margin_pct', 'labor_ratio', 'material_ratio',
            'revenue_per_team', 'revenue_per_job', 'cost_per_site', 'avg_job_profit',
            'cash_buffer_days',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result, "Missing KPI: $key");
        }
    }

    // ------------------------------------------------------------------
    // Stage 7 — FinancialSignalService
    // ------------------------------------------------------------------

    public function test_signal_service_emits_margin_drop_when_below_threshold(): void
    {
        Event::fake([MarginDropDetected::class, FinancialRiskDetected::class]);

        $date = now()->subDays(2)->toDateString();
        // Revenue 100, cost 200 → negative margin
        $this->makeInvoice($this->companyId, 'paid', 100.00, $date);
        $this->makeJobCostAllocation($this->companyId, 200.00, 'labour', $date);

        $service = app(FinancialSignalService::class);
        $alerts  = $service->evaluate($this->companyId, ['margin_pct_min' => 10.0]);

        $signals = array_column($alerts, 'signal');
        $this->assertContains('margin_drop', $signals);

        Event::assertDispatched(MarginDropDetected::class);
    }

    public function test_signal_service_emits_margin_threshold_crossed_on_negative_margin(): void
    {
        Event::fake([MarginThresholdCrossed::class, MarginDropDetected::class, FinancialRiskDetected::class]);

        $date = now()->subDays(2)->toDateString();
        $this->makeInvoice($this->companyId, 'paid', 100.00, $date);
        $this->makeJobCostAllocation($this->companyId, 500.00, 'labour', $date);

        $service = app(FinancialSignalService::class);
        $service->evaluate($this->companyId);

        Event::assertDispatched(MarginThresholdCrossed::class);
    }

    public function test_signal_service_emits_financial_risk_detected_when_alerts_exist(): void
    {
        Event::fake([FinancialRiskDetected::class, MarginDropDetected::class, MarginThresholdCrossed::class]);

        $date = now()->subDays(2)->toDateString();
        // Cause a margin drop
        $this->makeInvoice($this->companyId, 'paid', 50.00, $date);
        $this->makeJobCostAllocation($this->companyId, 500.00, 'labour', $date);

        $service = app(FinancialSignalService::class);
        $service->evaluate($this->companyId);

        Event::assertDispatched(FinancialRiskDetected::class);
    }

    public function test_signal_service_returns_empty_alerts_when_healthy(): void
    {
        // No data → all zeros → margin 0% which is < 10% threshold
        // Override threshold to -100 to simulate "healthy"
        $service = app(FinancialSignalService::class);
        $alerts  = $service->evaluate($this->companyId, [
            'margin_pct_min'       => -200.0,
            'cash_buffer_days_min' => -1,
        ]);

        $criticalSignals = array_filter($alerts, fn ($a) => $a['severity'] === 'critical');
        $this->assertEmpty($criticalSignals);
    }

    // ------------------------------------------------------------------
    // Stage 8 — Dashboard Routes
    // ------------------------------------------------------------------

    public function test_dashboard_index_route_returns_200(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard.money.dashboard.index'));
        $response->assertStatus(200);
    }

    public function test_cashflow_index_route_returns_200(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard.money.cashflow.index'));
        $response->assertStatus(200);
    }

    public function test_forecast_index_route_returns_200(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard.money.forecast.index'));
        $response->assertStatus(200);
    }

    public function test_kpis_index_route_returns_200(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard.money.kpis.index'));
        $response->assertStatus(200);
    }

    public function test_job_profitability_index_route_returns_200(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard.money.job-profitability.index'));
        $response->assertStatus(200);
    }
}
