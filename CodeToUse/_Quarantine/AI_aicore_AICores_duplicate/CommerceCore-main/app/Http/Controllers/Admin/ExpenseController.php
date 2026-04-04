<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    use ResolvesStore;

    public function index(Request $request)
    {
        $store   = $this->getActiveStore($request);
        $storeId = $store->id;

        $expenses = Expense::where('store_id', $storeId)
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('admin.expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('admin.expenses.create');
    }

    public function store(Request $request)
    {
        $store = $this->getActiveStore($request);

        $validated = $request->validate([
            'category'    => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'date'        => 'required|date',
        ]);

        $validated['store_id'] = $store->id;
        Expense::create($validated);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense logged!');
    }

    public function edit(Expense $expense)
    {
        return view('admin.expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        // Authorization: ensure expense belongs to active store
        $store = $this->getActiveStore($request);
        if ($expense->store_id !== $store->id && !$request->user()->isSuperAdmin()) {
            abort(403, 'This expense does not belong to your store.');
        }

        $validated = $request->validate([
            'category'    => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'date'        => 'required|date',
        ]);

        $expense->update($validated);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated!');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $store = $this->getActiveStore($request);
        if ($expense->store_id !== $store->id && !$request->user()->isSuperAdmin()) {
            abort(403, 'This expense does not belong to your store.');
        }

        $expense->delete();
        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted!');
    }
}
