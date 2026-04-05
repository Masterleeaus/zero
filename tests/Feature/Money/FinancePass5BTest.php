<?php

declare(strict_types=1);

namespace Tests\Feature\Money;

use App\Events\Money\BudgetCreated;
use App\Events\Money\RecommendationApproved;
use App\Events\Money\RecommendationCreated;
use App\Events\Money\RecommendationRejected;
use App\Models\Money\Budget;
use App\Models\Money\BudgetLine;
use App\Models\Money\Expense;
use App\Models\Money\FinancialActionRecommendation;
use App\Models\Money\Invoice;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBill;
use App\Models\User;
use App\Services\TitanMoney\BudgetVarianceService;
use App\Services\TitanMoney\FinancialActionRecommendationService;
use App\Services\TitanMoney\FinancialApprovalBridgeService;
use App\Services\TitanMoney\FinancialRiskEscalationService;
use App\Services\TitanMoney\ScenarioSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Finance Domain Pass 5B — Budgeting, Scenario Simulation, Approval-Driven Automation.
 *
 * Validates:
 *   - Budget and BudgetLine model creation
 *   - BudgetVarianceService: structure, cross-company isolation
 *   - ScenarioSimulationService: structure, no DB mutation
 *   - FinancialActionRecommendationService: recommendation creation and queue
 *   - FinancialApprovalBridgeService: approve/reject/dismiss lifecycle and events
 *   - FinancialRiskEscalationService: risk report structure
 *   - Route HTTP 200 for all 5B endpoints
 */
class FinancePass5BTest extends TestCase
{
    use RefreshDatabase;

    private int $companyId = 77;
    private int $otherCompanyId = 99;

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function makeUser(int $companyId = 0, string $role = 'admin'): User
    {
        return User::factory()->create([
            'company_id' => $companyId ?: $this->companyId,
            'role'       => $role,
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

    private function makeExpense(int $companyId, float $amount, string $date): Expense
    {
        return Expense::withoutGlobalScope('company')->create([
            'company_id'   => $companyId,
            'title'        => 'Test expense',
            'amount'       => $amount,
            'expense_date' => $date,
            'status'       => 'approved',
            'created_by'   => 1,
        ]);
    }

    private function makePayroll(int $companyId, float $totalGross, string $periodStart): Payroll
    {
        return Payroll::withoutGlobalScope('company')->create([
            'company_id'   => $companyId,
            'period_start' => $periodStart,
            'period_end'   => $periodStart,
            'pay_date'     => $periodStart,
            'total_gross'  => $totalGross,
            'total_tax'    => 0,
            'total_net'    => $totalGross,
            'status'       => 'approved',
            'created_by'   => 1,
        ]);
    }

    private function makeSupplierBill(int $companyId, float $totalAmount, string $status, string $billDate): SupplierBill
    {
        return SupplierBill::withoutGlobalScope('company')->create([
            'company_id'   => $companyId,
            'created_by'   => 1,
            'bill_date'    => $billDate,
            'total_amount' => $totalAmount,
            'amount_paid'  => $status === 'paid' ? $totalAmount : 0,
            'status'       => $status,
        ]);
    }

    private function makeBudget(int $companyId, string $status = 'active'): Budget
    {
        return Budget::withoutGlobalScope('company')->create([
            'company_id'  => $companyId,
            'name'        => 'Test Budget ' . uniqid(),
            'period_type' => 'monthly',
            'starts_at'   => now()->startOfMonth()->toDateString(),
            'ends_at'     => now()->endOfMonth()->toDateString(),
            'status'      => $status,
        ]);
    }

    private function makeBudgetLine(Budget $budget, string $lineType, float $amount): BudgetLine
    {
        return BudgetLine::create([
            'budget_id' => $budget->id,
            'line_type' => $lineType,
            'amount'    => $amount,
        ]);
    }

    private function makeRecommendation(int $companyId, string $status = 'pending_review'): FinancialActionRecommendation
    {
        return FinancialActionRecommendation::withoutGlobalScope('company')->create([
            'company_id'        => $companyId,
            'action_type'       => 'test_action',
            'title'             => 'Test Recommendation',
            'summary'           => 'Test summary',
            'reason'            => 'Test reason',
            'severity'          => 'high',
            'confidence'        => 80.0,
            'source_service'    => 'test',
            'status'            => $status,
            'created_by_system' => true,
        ]);
    }

    // ------------------------------------------------------------------
    // Stage 1 — Budget model
    // ------------------------------------------------------------------

    public function test_budget_can_be_created(): void
    {
        $budget = $this->makeBudget($this->companyId);

        $this->assertDatabaseHas('budgets', [
            'company_id' => $this->companyId,
            'status'     => 'active',
        ]);
        $this->assertEquals($this->companyId, $budget->company_id);
    }

    public function test_budget_line_can_be_created(): void
    {
        $budget = $this->makeBudget($this->companyId);
        $line   = $this->makeBudgetLine($budget, 'expense', 5000.00);

        $this->assertDatabaseHas('budget_lines', [
            'budget_id' => $budget->id,
            'line_type' => 'expense',
            'amount'    => '5000.00',
        ]);
        $this->assertEquals($budget->id, $line->budget_id);
    }

    public function test_budget_has_lines_relationship(): void
    {
        $budget = $this->makeBudget($this->companyId);
        $this->makeBudgetLine($budget, 'revenue', 10000.00);
        $this->makeBudgetLine($budget, 'expense', 4000.00);

        $this->assertCount(2, $budget->fresh()->lines);
    }

    // ------------------------------------------------------------------
    // Stage 2 — BudgetVarianceService
    // ------------------------------------------------------------------

    public function test_variance_report_has_correct_structure(): void
    {
        $budget = $this->makeBudget($this->companyId);
        $this->makeBudgetLine($budget, 'expense', 1000.00);

        $service = app(BudgetVarianceService::class);
        $report  = $service->variance($budget->id);

        $expectedKeys = [
            'budget_id', 'budget_name', 'company_id', 'period',
            'lines', 'total_budget', 'total_actual', 'total_variance',
            'total_variance_pct', 'generated_at',
        ];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $report, "Missing key: $key");
        }
        $this->assertEquals($budget->id, $report['budget_id']);
        $this->assertEquals($this->companyId, $report['company_id']);
    }

    public function test_variance_line_has_correct_structure(): void
    {
        $budget = $this->makeBudget($this->companyId);
        $this->makeBudgetLine($budget, 'expense', 1000.00);

        $service = app(BudgetVarianceService::class);
        $report  = $service->variance($budget->id);

        $this->assertNotEmpty($report['lines']);
        $line = $report['lines'][0];

        foreach (['id', 'line_type', 'budget', 'actual', 'variance', 'variance_percent', 'risk'] as $key) {
            $this->assertArrayHasKey($key, $line, "Missing line key: $key");
        }
        $this->assertEquals(1000.00, $line['budget']);
    }

    public function test_variance_actual_revenue_matches_paid_invoices(): void
    {
        $budget = $this->makeBudget($this->companyId);
        $this->makeBudgetLine($budget, 'revenue', 5000.00);

        $this->makeInvoice($this->companyId, 'paid', 3000.00, now()->toDateString());

        $service = app(BudgetVarianceService::class);
        $report  = $service->variance($budget->id);

        $revLine = collect($report['lines'])->firstWhere('line_type', 'revenue');
        $this->assertEquals(3000.00, $revLine['actual']);
        $this->assertEquals(-2000.00, $revLine['variance']);
    }

    public function test_variance_company_summary_returns_structure(): void
    {
        $budget = $this->makeBudget($this->companyId);
        $this->makeBudgetLine($budget, 'expense', 500.00);

        $service  = app(BudgetVarianceService::class);
        $summary  = $service->companySummary($this->companyId);

        $this->assertArrayHasKey('company_id', $summary);
        $this->assertArrayHasKey('budget_count', $summary);
        $this->assertArrayHasKey('budgets', $summary);
        $this->assertEquals(1, $summary['budget_count']);
    }

    public function test_variance_company_summary_only_includes_active_budgets(): void
    {
        $this->makeBudget($this->companyId, 'draft');
        $this->makeBudget($this->companyId, 'active');

        $service  = app(BudgetVarianceService::class);
        $summary  = $service->companySummary($this->companyId);

        $this->assertEquals(1, $summary['budget_count']);
    }

    // ------------------------------------------------------------------
    // Stage 3 — ScenarioSimulationService
    // ------------------------------------------------------------------

    public function test_simulation_returns_correct_structure(): void
    {
        $service = app(ScenarioSimulationService::class);
        $result  = $service->simulate($this->companyId, 'customer_churn');

        $expectedKeys = [
            'company_id', 'scenario_type', 'parameters', 'horizon_days',
            'simulated_at', 'baseline', 'adjusted', 'impact',
        ];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result, "Missing key: $key");
        }
        $this->assertEquals($this->companyId, $result['company_id']);
        $this->assertEquals('customer_churn', $result['scenario_type']);
    }

    public function test_simulation_baseline_has_all_fields(): void
    {
        $service  = app(ScenarioSimulationService::class);
        $result   = $service->simulate($this->companyId, 'lower_utilization');
        $baseline = $result['baseline'];

        foreach (['projected_revenue', 'projected_costs', 'projected_margin', 'margin_pct', 'payables_pressure', 'receivables_at_risk'] as $key) {
            $this->assertArrayHasKey($key, $baseline, "Missing baseline key: $key");
        }
    }

    public function test_simulation_does_not_mutate_database(): void
    {
        $beforeCount = FinancialActionRecommendation::count();

        $service = app(ScenarioSimulationService::class);
        $service->simulate($this->companyId, 'fuel_cost_spike', ['spike_pct' => 30]);

        $this->assertEquals($beforeCount, FinancialActionRecommendation::count());
    }

    public function test_simulation_supplier_price_increase_raises_costs(): void
    {
        $this->makeExpense($this->companyId, 1000.00, now()->toDateString());

        $service  = app(ScenarioSimulationService::class);
        $result   = $service->simulate($this->companyId, 'supplier_price_increase', ['increase_pct' => 20]);

        $this->assertGreaterThanOrEqual(
            $result['baseline']['projected_costs'],
            $result['adjusted']['projected_costs']
        );
    }

    public function test_compare_scenarios_returns_multiple_results(): void
    {
        $service = app(ScenarioSimulationService::class);
        $result  = $service->compareScenarios($this->companyId, [
            ['type' => 'customer_churn', 'parameters' => ['churn_pct' => 10]],
            ['type' => 'fuel_cost_spike', 'parameters' => ['spike_pct' => 15]],
        ]);

        $this->assertArrayHasKey('scenarios', $result);
        $this->assertCount(2, $result['scenarios']);
    }

    // ------------------------------------------------------------------
    // Stage 4 — FinancialActionRecommendationService
    // ------------------------------------------------------------------

    public function test_recommendation_can_be_created(): void
    {
        Event::fake([RecommendationCreated::class]);

        $service = app(FinancialActionRecommendationService::class);
        $rec = $service->recommend(
            companyId: $this->companyId,
            actionType: 'test_action',
            title: 'Test Title',
            summary: 'Test summary',
            reason: 'Test reason',
            severity: 'high',
        );

        $this->assertInstanceOf(FinancialActionRecommendation::class, $rec);
        $this->assertEquals($this->companyId, $rec->company_id);
        $this->assertEquals('pending_review', $rec->status);
        Event::assertDispatched(RecommendationCreated::class);
    }

    public function test_pending_returns_only_pending_recommendations(): void
    {
        $this->makeRecommendation($this->companyId, 'pending_review');
        $this->makeRecommendation($this->companyId, 'approved');

        $service = app(FinancialActionRecommendationService::class);
        $pending = $service->pending($this->companyId);

        $this->assertCount(1, $pending);
        $this->assertEquals('pending_review', $pending->first()->status);
    }

    public function test_pending_is_company_scoped(): void
    {
        $this->makeRecommendation($this->companyId, 'pending_review');
        $this->makeRecommendation($this->otherCompanyId, 'pending_review');

        $service = app(FinancialActionRecommendationService::class);
        $pending = $service->pending($this->companyId);

        $this->assertCount(1, $pending);
        $this->assertEquals($this->companyId, $pending->first()->company_id);
    }

    public function test_generate_from_variance_creates_recommendations_for_high_risk(): void
    {
        Event::fake([RecommendationCreated::class]);

        $varianceReport = [
            'lines' => [
                ['line_type' => 'expense', 'risk' => 'high', 'variance_percent' => 25],
                ['line_type' => 'labor',   'risk' => 'critical', 'variance_percent' => 45],
                ['line_type' => 'revenue', 'risk' => 'low', 'variance_percent' => 3],
            ],
        ];

        $service = app(FinancialActionRecommendationService::class);
        $created = $service->generateFromVariance($this->companyId, $varianceReport);

        $this->assertCount(2, $created);
    }

    // ------------------------------------------------------------------
    // Stage 5 — FinancialApprovalBridgeService
    // ------------------------------------------------------------------

    public function test_approve_updates_status_and_dispatches_event(): void
    {
        Event::fake([RecommendationApproved::class]);

        $rec  = $this->makeRecommendation($this->companyId);
        $user = $this->makeUser($this->companyId);

        $service = app(FinancialApprovalBridgeService::class);
        $updated = $service->approve($rec->id, $user, 'Looks good');

        $this->assertEquals('approved', $updated->status);
        $this->assertEquals($user->id, $updated->reviewed_by);
        $this->assertEquals('Looks good', $updated->review_notes);
        Event::assertDispatched(RecommendationApproved::class);
    }

    public function test_reject_updates_status_and_dispatches_event(): void
    {
        Event::fake([RecommendationRejected::class]);

        $rec  = $this->makeRecommendation($this->companyId);
        $user = $this->makeUser($this->companyId);

        $service = app(FinancialApprovalBridgeService::class);
        $updated = $service->reject($rec->id, $user, 'Not valid');

        $this->assertEquals('rejected', $updated->status);
        Event::assertDispatched(RecommendationRejected::class);
    }

    public function test_dismiss_updates_status(): void
    {
        $rec  = $this->makeRecommendation($this->companyId);
        $user = $this->makeUser($this->companyId);

        $service = app(FinancialApprovalBridgeService::class);
        $updated = $service->dismiss($rec->id, $user, 'Not relevant');

        $this->assertEquals('dismissed', $updated->status);
        $this->assertNotNull($updated->reviewed_at);
    }

    public function test_queue_returns_only_pending_for_company(): void
    {
        $this->makeRecommendation($this->companyId, 'pending_review');
        $this->makeRecommendation($this->companyId, 'approved');
        $this->makeRecommendation($this->otherCompanyId, 'pending_review');

        $service = app(FinancialApprovalBridgeService::class);
        $queue   = $service->queue($this->companyId);

        $this->assertCount(1, $queue);
        $this->assertEquals($this->companyId, $queue->first()->company_id);
    }

    public function test_audit_trail_returns_reviewed_recommendations(): void
    {
        $rec  = $this->makeRecommendation($this->companyId, 'pending_review');
        $user = $this->makeUser($this->companyId);

        $service = app(FinancialApprovalBridgeService::class);
        $service->approve($rec->id, $user);

        $trail = $service->auditTrail($this->companyId);
        $this->assertCount(1, $trail);
        $this->assertNotNull($trail->first()->reviewed_at);
    }

    // ------------------------------------------------------------------
    // Stage 6 — FinancialRiskEscalationService
    // ------------------------------------------------------------------

    public function test_risk_evaluation_returns_correct_structure(): void
    {
        $service = app(FinancialRiskEscalationService::class);
        $result  = $service->evaluate($this->companyId);

        $this->assertArrayHasKey('company_id', $result);
        $this->assertArrayHasKey('evaluated_at', $result);
        $this->assertArrayHasKey('risk_count', $result);
        $this->assertArrayHasKey('risks', $result);
        $this->assertEquals($this->companyId, $result['company_id']);
    }

    public function test_payables_pressure_detected_when_above_threshold(): void
    {
        Event::fake();

        $this->makeSupplierBill($this->companyId, 20000.00, 'approved', now()->toDateString());

        $service = app(FinancialRiskEscalationService::class);
        $result  = $service->evaluate($this->companyId, ['payables_threshold' => 10000]);

        $types = array_column($result['risks'], 'type');
        $this->assertContains('payables_pressure', $types);
    }

    public function test_no_payables_risk_below_threshold(): void
    {
        $service = app(FinancialRiskEscalationService::class);
        $result  = $service->evaluate($this->companyId, ['payables_threshold' => 999999]);

        $types = array_column($result['risks'], 'type');
        $this->assertNotContains('payables_pressure', $types);
    }

    public function test_risk_evaluation_is_company_scoped(): void
    {
        $this->makeSupplierBill($this->otherCompanyId, 50000.00, 'approved', now()->toDateString());

        $service = app(FinancialRiskEscalationService::class);
        $result  = $service->evaluate($this->companyId, ['payables_threshold' => 10000]);

        $types = array_column($result['risks'], 'type');
        $this->assertNotContains('payables_pressure', $types);
    }

    // ------------------------------------------------------------------
    // Stage 7 — Cross-company leakage checks
    // ------------------------------------------------------------------

    public function test_budget_variance_not_leaked_across_companies(): void
    {
        $otherBudget = Budget::withoutGlobalScope('company')->create([
            'company_id'  => $this->otherCompanyId,
            'name'        => 'Other Company Budget',
            'period_type' => 'monthly',
            'starts_at'   => now()->startOfMonth()->toDateString(),
            'ends_at'     => now()->endOfMonth()->toDateString(),
            'status'      => 'active',
        ]);

        $service = app(BudgetVarianceService::class);
        $summary = $service->companySummary($this->companyId);

        $ids = array_column($summary['budgets'], 'budget_id');
        $this->assertNotContains($otherBudget->id, $ids);
    }

    // ------------------------------------------------------------------
    // Stage 8 — Route HTTP 200 tests
    // ------------------------------------------------------------------

    public function test_budgets_index_returns_200(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard.money.budgets.index'));
        $response->assertStatus(200);
    }

    public function test_budget_variance_index_returns_200(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard.money.budget-variance.index'));
        $response->assertStatus(200);
    }

    public function test_scenarios_index_returns_200(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard.money.scenarios.index'));
        $response->assertStatus(200);
    }

    public function test_recommendations_index_returns_200(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard.money.recommendations.index'));
        $response->assertStatus(200);
    }

    public function test_recommendation_review_returns_200(): void
    {
        $user = $this->makeUser();
        $rec  = $this->makeRecommendation($this->companyId);
        $this->actingAs($user);

        $response = $this->get(route('dashboard.money.recommendations.review', $rec));
        $response->assertStatus(200);
    }
}
