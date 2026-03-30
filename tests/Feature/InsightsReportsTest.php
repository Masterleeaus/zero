<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Core\Insights\InsightsController;
use App\Models\Crm\Customer;
use App\Models\Money\Expense;
use App\Models\Money\ExpenseCategory;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\User;
use App\Models\Work\Leave;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\TestCase;

class InsightsReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_collects_company_scoped_metrics(): void
    {
        $companyId = 77;
        $user = User::factory()->create(['company_id' => $companyId]);
        $this->actingAs($user);

        $customer = Customer::factory()->create(['company_id' => $companyId, 'name' => 'Report Customer']);
        $quote = Quote::factory()->create(['company_id' => $companyId]);

        Invoice::factory()->create([
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'quote_id' => $quote->id,
            'status' => 'paid',
            'total' => 250,
            'balance' => 0,
            'updated_at' => now(),
        ]);

        Invoice::factory()->create([
            'company_id' => $companyId + 1,
            'customer_id' => Customer::factory()->create(['company_id' => $companyId + 1])->id,
            'quote_id' => Quote::factory()->create(['company_id' => $companyId + 1])->id,
            'status' => 'paid',
            'total' => 999,
            'balance' => 0,
            'updated_at' => now(),
        ]);

        ServiceJob::factory()->create([
            'company_id' => $companyId,
            'status' => 'open',
            'site_id' => Site::factory(['company_id' => $companyId]),
        ]);

        ServiceJob::factory()->create([
            'company_id' => $companyId + 1,
            'status' => 'completed',
            'site_id' => Site::factory(['company_id' => $companyId + 1]),
        ]);

        Leave::factory()->create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'type' => 'sick',
            'start_date' => now()->startOfMonth()->addDay(),
            'end_date' => now()->startOfMonth()->addDays(2),
        ]);

        Leave::factory()->create([
            'company_id' => $companyId + 1,
            'type' => 'annual',
            'start_date' => now()->startOfMonth()->addDay(),
            'end_date' => now()->startOfMonth()->addDays(2),
        ]);

        $category = ExpenseCategory::factory()->create(['company_id' => $companyId]);
        $expenseUser = User::factory()->create(['company_id' => $companyId]);

        Expense::factory()->create([
            'company_id' => $companyId,
            'expense_category_id' => $category->id,
            'created_by' => $expenseUser->id,
            'expense_date' => now()->toDateString(),
            'amount' => 80,
        ]);

        $request = Request::create('/dashboard/insights/reports');
        $request->setUserResolver(fn () => $user);

        $controller = new InsightsController();
        /** @var View $view */
        $view = $controller->reports($request);
        $data = $view->getData();

        $this->assertNotEmpty($data['revenueReport']);
        $this->assertEquals(250.0, (float) $data['revenueReport']->first()->revenue);
        $this->assertSame(['open' => 1], $data['jobsByStatus']);
        $this->assertEquals(1, $data['topCustomers']->count());
        $this->assertEquals('Report Customer', $data['topCustomers']->first()->name);
        $this->assertEquals(250.0, (float) $data['topCustomers']->first()->total_paid);
        $this->assertEquals(1, $data['leaveSummary']['sick']);
        $this->assertNotEmpty($data['expenseVsRevenue']);
    }
}
