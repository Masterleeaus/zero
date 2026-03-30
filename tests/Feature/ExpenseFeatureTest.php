<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Money\Expense;
use App\Models\Money\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_creation_is_scoped_to_company(): void
    {
        $user = User::factory()->create(['company_id' => 11]);
        $category = ExpenseCategory::factory()->create(['company_id' => 11]);

        $this->actingAs($user);

        $response = $this->post(route('dashboard.money.expenses.store'), [
            'title' => 'Laptop stand',
            'expense_category_id' => $category->id,
            'amount' => 120.50,
            'expense_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('expenses', [
            'title' => 'Laptop stand',
            'company_id' => 11,
            'expense_category_id' => $category->id,
        ]);
    }

    public function test_expense_metrics_available_in_insights(): void
    {
        $user = User::factory()->create(['company_id' => 13]);
        $category = ExpenseCategory::factory()->create(['company_id' => 13]);
        Expense::factory()->create([
            'company_id' => $user->company_id,
            'expense_category_id' => $category->id,
            'amount' => 50,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard.insights.overview'));
        $response->assertOk();
        $response->assertViewHas('expenseTotal', 50.0);
    }
}
