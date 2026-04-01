<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends CoreController
{
    public function index(Request $request): View
    {
        $categories = ExpenseCategory::query()
            ->latest()
            ->paginate(20);

        return view('default.panel.user.money.expense-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('default.panel.user.money.expense-categories.create', [
            'category' => new ExpenseCategory(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories')->where('company_id', $request->user()->company_id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        ExpenseCategory::create([
            ...$data,
            'company_id' => $request->user()->company_id,
        ]);

        return redirect()->route('dashboard.money.expense-categories.index')
            ->with('message', __('Category created'));
    }

    public function edit(Request $request, ExpenseCategory $expenseCategory): View
    {
        abort_if($expenseCategory->company_id !== $request->user()->company_id, 403);

        return view('default.panel.user.money.expense-categories.edit', [
            'category' => $expenseCategory,
        ]);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        abort_if($expenseCategory->company_id !== $request->user()->company_id, 403);

        $data = $request->validate([
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($expenseCategory->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $expenseCategory->update($data);

        return redirect()->route('dashboard.money.expense-categories.index')
            ->with('message', __('Category updated'));
    }

    public function destroy(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        abort_if($expenseCategory->company_id !== $request->user()->company_id, 403);

        $expenseCategory->delete();

        return back()->with('message', __('Category deleted'));
    }
}
