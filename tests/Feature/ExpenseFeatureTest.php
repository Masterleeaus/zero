<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Money\Expense;
use App\Models\Money\ExpenseCategory;
use App\Notifications\LiveNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
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
            'status' => 'pending',
        ]);

        $expense = Expense::first();
        $this->assertEquals('pending', $expense->status);
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

    public function test_expense_totals_by_category_are_scoped(): void
    {
        $user = User::factory()->create(['company_id' => 20]);
        $category = ExpenseCategory::factory()->create(['company_id' => 20]);
        Expense::factory()->create([
            'company_id' => $user->company_id,
            'expense_category_id' => $category->id,
            'amount' => 25,
        ]);
        Expense::factory()->create([
            'company_id' => $user->company_id,
            'expense_category_id' => $category->id,
            'amount' => 75,
        ]);
        Expense::factory()->create(); // other company

        $this->assertSame(
            [ $category->id => 100.0 ],
            Expense::totalsByCategory($user->company_id)
        );
    }

    public function test_admin_can_approve_expense(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['company_id' => 30]);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $submitter = User::factory()->create(['company_id' => 30]);
        $category = ExpenseCategory::factory()->create(['company_id' => 30]);
        $expense = Expense::factory()->create([
            'company_id' => 30,
            'expense_category_id' => $category->id,
            'created_by' => $submitter->id,
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('dashboard.money.expenses.approve', $expense));
        $response->assertRedirect();

        $expense->refresh();

        $this->assertEquals('approved', $expense->status);
        $this->assertEquals($admin->id, $expense->approved_by);
        $this->assertNotNull($expense->approved_at);
        $this->assertNull($expense->rejection_reason);

        Notification::assertSentTo($submitter, LiveNotification::class);
    }

    public function test_admin_can_reject_expense_with_reason(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['company_id' => 40]);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $submitter = User::factory()->create(['company_id' => 40]);
        $category = ExpenseCategory::factory()->create(['company_id' => 40]);
        $expense = Expense::factory()->create([
            'company_id' => 40,
            'expense_category_id' => $category->id,
            'created_by' => $submitter->id,
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('dashboard.money.expenses.reject', $expense), [
            'reason' => 'Out of policy',
        ]);
        $response->assertRedirect();

        $expense->refresh();

        $this->assertEquals('rejected', $expense->status);
        $this->assertNull($expense->approved_by);
        $this->assertNull($expense->approved_at);
        $this->assertEquals('Out of policy', $expense->rejection_reason);

        Notification::assertSentTo($submitter, LiveNotification::class);
    }

    public function test_expense_category_must_be_unique_per_company(): void
    {
        $user = User::factory()->create(['company_id' => 50]);
        ExpenseCategory::factory()->create([
            'company_id' => 50,
            'name' => 'Travel',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('dashboard.money.expense-categories.store'), [
            'name' => 'Travel',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('expense_categories', 1);
    }

    public function test_expense_category_can_repeat_across_companies(): void
    {
        $firstUser = User::factory()->create(['company_id' => 60]);
        $secondUser = User::factory()->create(['company_id' => 61]);

        $this->actingAs($firstUser);
        $this->post(route('dashboard.money.expense-categories.store'), [
            'name' => 'Equipment',
        ])->assertRedirect();

        $this->actingAs($secondUser);
        $this->post(route('dashboard.money.expense-categories.store'), [
            'name' => 'Equipment',
        ])->assertRedirect();

        $this->assertDatabaseHas('expense_categories', [
            'company_id' => 60,
            'name' => 'Equipment',
        ]);
        $this->assertDatabaseHas('expense_categories', [
            'company_id' => 61,
            'name' => 'Equipment',
        ]);
    }
}
