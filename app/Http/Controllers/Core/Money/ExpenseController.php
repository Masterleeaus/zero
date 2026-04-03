<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Actions\Notify;
use App\Events\Money\ExpenseApproved;
use App\Http\Controllers\Core\CoreController;
use App\Models\User;
use App\Models\Money\Expense;
use App\Models\Money\ExpenseCategory;
use App\Notifications\LiveNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends CoreController
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $expenses = Expense::query()
            ->where('company_id', $companyId)
            ->with('category')
            ->betweenDates($request->get('start_date'), $request->get('end_date'))
            ->latest('expense_date')
            ->paginate(25)
            ->withQueryString();

        $categories = $this->categories($companyId);

        return view('default.panel.user.money.expenses.index', compact('expenses', 'categories'));
    }

    public function show(Request $request, Expense $expense): View
    {
        abort_if($expense->company_id !== $request->user()->company_id, 403);

        $expense->load(['category', 'createdBy', 'approver']);

        return view('default.panel.user.money.expenses.show', [
            'expense' => $expense,
        ]);
    }

    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;

        return view('default.panel.user.money.expenses.create', [
            'expense'    => new Expense(),
            'categories' => $this->categories($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $data = $this->validated($request, $companyId);

        Expense::create([
            ...$data,
            'company_id' => $companyId,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('dashboard.money.expenses.index')
            ->with('message', __('Expense created'));
    }

    public function approve(Request $request, Expense $expense): RedirectResponse
    {
        abort_if($expense->company_id !== $request->user()->company_id, 403);
        abort_unless($request->user()->isAdmin(), 403);

        $expense->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        event(new ExpenseApproved($expense));

        if ($expense->created_by && $expense->created_by !== $request->user()->id) {
            Notify::to(
                $expense->created_by,
                __('Expense Approved'),
                __('Your expense ":title" has been approved.', ['title' => $expense->title]),
                route('dashboard.money.expenses.show', $expense)
            );
        }

        return redirect()->route('dashboard.money.expenses.show', $expense)
            ->with('message', __('Expense approved'));
    }

    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        abort_if($expense->company_id !== $request->user()->company_id, 403);
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $expense->update([
            'status'           => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
            'approved_by'      => $request->user()->id,
            'approved_at'      => now(),
        ]);

        if ($expense->created_by && $expense->created_by !== $request->user()->id) {
            Notify::to(
                $expense->created_by,
                __('Expense Rejected'),
                __('Your expense ":title" has been rejected.', ['title' => $expense->title]),
                route('dashboard.money.expenses.show', $expense)
            );
        }

        return redirect()->route('dashboard.money.expenses.show', $expense)
            ->with('message', __('Expense rejected'));
    }

    public function edit(Request $request, Expense $expense): View
    {
        abort_if($expense->company_id !== $request->user()->company_id, 403);

        return view('default.panel.user.money.expenses.edit', [
            'expense'    => $expense,
            'categories' => $this->categories($request->user()->company_id),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        abort_if($expense->company_id !== $request->user()->company_id, 403);

        $data = $this->validated($request, $expense->company_id);

        $expense->update($data);

        return redirect()->route('dashboard.money.expenses.index')
            ->with('message', __('Expense updated'));
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        abort_if($expense->company_id !== $request->user()->company_id, 403);

        $expense->delete();

        return back()->with('message', __('Expense deleted'));
    }

    protected function categories(int $companyId)
    {
        return ExpenseCategory::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();
    }

    protected function validated(Request $request, int $companyId): array
    {
        $data = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'expense_category_id' => ['nullable', 'exists:expense_categories,id'],
            'amount'              => ['required', 'numeric', 'min:0'],
            'expense_date'        => ['nullable', 'date'],
            'notes'               => ['nullable', 'string'],
        ]);

        if (! empty($data['expense_category_id'])) {
            $categoryCompany = ExpenseCategory::query()
                ->where('company_id', $companyId)
                ->where('id', $data['expense_category_id'])
                ->exists();

            abort_unless($categoryCompany, 403);
        }

        return $data;
    }
}
