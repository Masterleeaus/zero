<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\User;
use App\Models\Money\Expense;
use App\Models\Money\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

        $submitter = User::factory()->create(['company_id' => 30, 'type' => Roles::USER]);
        $admin = User::factory()->create(['company_id' => 30, 'type' => Roles::ADMIN]);
        $category = ExpenseCategory::factory()->create(['company_id' => 30]);
        $expense = Expense::factory()->create([
            'company_id'          => 30,
            'expense_category_id' => $category->id,
            'created_by'          => $submitter->id,
            'status'              => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('dashboard.money.expenses.approve', $expense));

        $response->assertRedirect(route('dashboard.money.expenses.show', $expense));

        $this->assertDatabaseHas('expenses', [
            'id'          => $expense->id,
            'status'      => 'approved',
            'approved_by' => $admin->id,
        ]);

        Notification::assertSentTo($submitter, \App\Notifications\LiveNotification::class);
    }

    public function test_admin_can_reject_expense(): void
    {
        Notification::fake();

        $submitter = User::factory()->create(['company_id' => 31, 'type' => Roles::USER]);
        $admin = User::factory()->create(['company_id' => 31, 'type' => Roles::ADMIN]);
        $category = ExpenseCategory::factory()->create(['company_id' => 31]);
        $expense = Expense::factory()->create([
            'company_id'          => 31,
            'expense_category_id' => $category->id,
            'created_by'          => $submitter->id,
            'status'              => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('dashboard.money.expenses.reject', $expense), [
            'rejection_reason' => 'Missing receipts',
        ]);

        $response->assertRedirect(route('dashboard.money.expenses.show', $expense));

        $this->assertDatabaseHas('expenses', [
            'id'               => $expense->id,
            'status'           => 'rejected',
            'rejection_reason' => 'Missing receipts',
            'approved_by'      => $admin->id,
        ]);

        Notification::assertSentTo($submitter, \App\Notifications\LiveNotification::class);
    }

    public function test_non_admin_cannot_approve_expense(): void
    {
        $user = User::factory()->create(['company_id' => 32, 'type' => Roles::USER]);
        $category = ExpenseCategory::factory()->create(['company_id' => 32]);
        $expense = Expense::factory()->create([
            'company_id'          => 32,
            'expense_category_id' => $category->id,
            'status'              => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('dashboard.money.expenses.approve', $expense));
        $response->assertForbidden();
    }

    public function test_reject_requires_reason(): void
    {
        $admin = User::factory()->create(['company_id' => 33, 'type' => Roles::ADMIN]);
        $category = ExpenseCategory::factory()->create(['company_id' => 33]);
        $expense = Expense::factory()->create([
            'company_id'          => 33,
            'expense_category_id' => $category->id,
            'status'              => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('dashboard.money.expenses.reject', $expense), []);
        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_expense_category_names_are_unique_per_company(): void
    {
        $user = User::factory()->create(['company_id' => 40]);
        ExpenseCategory::factory()->create(['company_id' => 40, 'name' => 'Travel']);

        $this->actingAs($user);

        $response = $this->post(route('dashboard.money.expense-categories.store'), [
            'name' => 'Travel',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_expense_category_names_are_not_globally_unique(): void
    {
        $user = User::factory()->create(['company_id' => 41]);
        // Category exists for a different company
        ExpenseCategory::factory()->create(['company_id' => 99, 'name' => 'Travel']);

        $this->actingAs($user);

        $response = $this->post(route('dashboard.money.expense-categories.store'), [
            'name' => 'Travel',
        ]);

        $response->assertRedirect(route('dashboard.money.expense-categories.index'));
        $this->assertDatabaseHas('expense_categories', ['company_id' => 41, 'name' => 'Travel']);
    }
}
