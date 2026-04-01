<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::orderBy('code')->get();
        $groupedAccounts = $accounts->groupBy('gl_type');
        return view('admin.accounts.index', compact('accounts', 'groupedAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:accounts',
            'gl_type' => 'required|in:asset,liability,equity,revenue,expense',
            'type' => 'required|string',
            'parent_id' => 'nullable|exists:accounts,id',
            'account_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'balance' => 'required|numeric'
        ]);

        Account::create($validated);
        return back()->with('success', 'Account created successfully.');
    }
}
