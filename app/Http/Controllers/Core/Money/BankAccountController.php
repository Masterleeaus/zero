<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Support\WorkcoreDemoData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankAccountController extends CoreController
{
    public function index(): View
    {
        return view('default.panel.user.money.bank-accounts.index', [
            'accounts' => WorkcoreDemoData::bankAccounts(),
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.money.bank-accounts.form', [
            'account' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Bank account created.'),
        ]);
    }

    public function show(string $account): View
    {
        return view('default.panel.user.money.bank-accounts.show', [
            'account' => WorkcoreDemoData::bankAccounts()->firstWhere('last4', $account)
                ?? WorkcoreDemoData::bankAccounts()->first(),
        ]);
    }

    public function edit(string $account): View
    {
        return view('default.panel.user.money.bank-accounts.form', [
            'account' => WorkcoreDemoData::bankAccounts()->firstWhere('last4', $account),
        ]);
    }

    public function update(Request $request, string $account): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Bank account :account updated.', ['account' => $account]),
        ]);
    }

    public function destroy(string $account): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Bank account :account removed.', ['account' => $account]),
        ]);
    }

    public function setDefault(string $account): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Bank account :account set as default.', ['account' => $account]),
        ]);
    }
}
