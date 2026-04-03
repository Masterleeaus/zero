<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Controller;
use App\Models\Money\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $accounts        = Account::orderBy('code')->orderBy('name')->get();
        $groupedAccounts = $accounts->groupBy('gl_type');

        return view('default.panel.user.money.accounts.index', compact('accounts', 'groupedAccounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:20',
            'gl_type'        => 'required|in:asset,liability,equity,revenue,expense',
            'type'           => 'required|string|max:50',
            'parent_id'      => 'nullable|exists:accounts,id',
            'balance'        => 'required|numeric',
            'account_number' => 'nullable|string|max:100',
            'bank_name'      => 'nullable|string|max:100',
        ]);

        Account::create($validated);

        return back()->with('success', __('Account created successfully.'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:20',
            'gl_type'        => 'required|in:asset,liability,equity,revenue,expense',
            'type'           => 'required|string|max:50',
            'parent_id'      => 'nullable|exists:accounts,id',
            'is_active'      => 'boolean',
            'account_number' => 'nullable|string|max:100',
            'bank_name'      => 'nullable|string|max:100',
        ]);

        $account->update($validated);

        return back()->with('success', __('Account updated successfully.'));
    }

    public function destroy(Account $account): RedirectResponse
    {
        if ($account->journalLines()->exists() || $account->ledgerTransactions()->exists()) {
            return back()->with('error', __('Cannot delete an account that has journal lines or transactions.'));
        }

        $account->delete();

        return back()->with('success', __('Account deleted successfully.'));
    }
}
