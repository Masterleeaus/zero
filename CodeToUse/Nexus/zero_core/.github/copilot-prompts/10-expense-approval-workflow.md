# Copilot Task: Complete Expense Approval Workflow

## Context
Laravel 10 SaaS. The `Expense` model and controller exist but the approval workflow is incomplete.
Expenses need: submission → pending review → approved/rejected → (if approved) link to payment.

## Files
- `app/Models/Money/Expense.php`
- `app/Http/Controllers/Core/Money/ExpenseController.php`
- `resources/views/default/panel/user/money/expenses/`
- Relevant migration: `database/migrations/*create_expenses*`

## Your Task

### 1. Check current Expense status values
Read the `Expense` model and migration to see what `status` values exist.
If `pending`, `approved`, `rejected` aren't present, add a migration:
```php
// In migration up():
$table->string('status')->default('pending')->change(); // or add column
$table->unsignedBigInteger('approved_by')->nullable()->after('status');
$table->timestamp('approved_at')->nullable()->after('approved_by');
$table->text('rejection_reason')->nullable()->after('approved_at');
```

### 2. Add `approve()` and `reject()` methods to ExpenseController
```php
public function approve(Expense $expense): RedirectResponse
{
    $this->authorize('update', $expense);

    $expense->update([
        'status'      => 'approved',
        'approved_by' => auth()->id(),
        'approved_at' => now(),
    ]);

    // Notify submitter
    $submitter = User::find($expense->created_by);
    $submitter?->notify(new LiveNotification(
        message: "Your expense '{$expense->description}' has been approved.",
        link: route('dashboard.money.expenses.show', $expense),
        title: 'Expense Approved'
    ));

    return back()->with('status', __('Expense approved.'));
}

public function reject(Request $request, Expense $expense): RedirectResponse
{
    $this->authorize('update', $expense);

    $expense->update([
        'status'           => 'rejected',
        'rejection_reason' => $request->input('reason'),
    ]);

    $submitter = User::find($expense->created_by);
    $submitter?->notify(new LiveNotification(
        message: "Your expense '{$expense->description}' was rejected.",
        link: route('dashboard.money.expenses.show', $expense),
        title: 'Expense Rejected'
    ));

    return back()->with('status', __('Expense rejected.'));
}
```

### 3. Add routes for approve/reject
In `routes/core/money.routes.php`:
```php
Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
Route::post('expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');
```

### 4. Update the expense show/index views
- Show current status as a badge (pending=yellow, approved=green, rejected=red)
- Show Approve/Reject buttons for managers (check role: `auth()->user()->hasRole('admin')`)
- Show rejection reason when status = rejected
- Show approver name and date when status = approved

### 5. Fix ExpenseCategory scoping
In `ExpenseCategoryController::store()`, ensure categories are validated against the same company:
```php
'name' => [
    'required', 'string', 'max:100',
    Rule::unique('expense_categories')->where('company_id', auth()->user()->company_id),
],
```

## Constraints
- Only admins/managers should see Approve/Reject buttons
- Company scoped throughout
- Add `approved_by` FK constraint to `users` table if adding column
